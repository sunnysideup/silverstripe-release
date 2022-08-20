<?php

$obj = new ReleaseProjectFromBitbucketHook();
$obj->run();

class ReleaseProjectFromBitbucketHook
{
    /**
     * The directory where the .env file can be located.
     *
     * @var string
     */
    protected $path = '.env';

    private $allow = false;

    private $ip = '';

    private $allowedCIDRS = [
        '127.0.0.1/0',
    ];

    private $webhookSecret = '';

    private $webhookSecretProvided = '';

    private $releaseScript = '';

    private $absoluteDirOfThisScript = '';

    private $envType = '';

    public function run()
    {
        if ($this->getVars()) {
            if ($this->securityChecks()) {
                $this->runInner();
            } else {
                $this->abort('wrong credentials');
            }
        } else {
            $this->abort('no getVars');
        }
    }

    protected function getVars(): bool
    {
        $this->absoluteDirOfThisScript = realpath(dirname(__FILE__));
        $this->findDotEnvFile();
        $this->loadDotEnvFile();
        $this->envType = getenv('SS_ENVIRONMENT_TYPE');
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->webhookSecret = getenv('SS_RELEASE_TOKEN');
        $this->releaseScript = getenv('SS_RELEASE_SCRIPT');
        $this->webhookSecretProvided = empty($_GET['ts']) ? '' : $_GET['ts'];

        return $this->basicCheck();
    }

    protected function securityChecks()
    {
        $this->getAtlassionIpRanges();

        /*
         * (1) Primary check
         *
         * Block if for some reason we couldn't get a response from Atlassian.
         */
        if (empty($this->allowedCIDRS)) {
            $this->abort('No ranges found for bitbucket');

            return false;
        }

        /*
         * (2) Secondary check (Additional/Optional)
         *
         * Token validation, that comes from the Bitbucket Webhook setup
         */
        if ($this->webhookSecretProvided !== $this->webhookSecret) {
            $this->abort('Token does not match: ');

            return false;
        }

        /**
         * (3) Tertiary check.
         *
         * IP validation - only allow requests for Atlassian IPs
         */
        $allow = false;
        foreach ($this->allowedCIDRS as $cidr) {
            $allow = $this->isIpInRange($cidr);
            if ($allow) {
                break;
            }
        }

        if (! $allow) {
            $this->abort('Ip (' . $this->ip . ') not in range');

            return false;
        }

        return true;
    }

    protected function runInner()
    {
        $optionA = $this->releaseScript;
        $optionB = $this->absoluteDirOfThisScript . '/' . $this->releaseScript;
        $optionC = '../../../../' . $this->releaseScript;
        foreach ([$optionA, $optionB, $optionC] as $scriptPath) {
            if (file_exists($scriptPath)) {
                $scriptPath = realpath($scriptPath);
                if (is_executable($scriptPath)) {
                    echo '<h1>' . $scriptPath . '</h1>';
                    $output = shell_exec('bash ' . $scriptPath . ' 2>&1');
                    echo "<pre>{$output}</pre>";
                    die('<h1>DONE</h1>');
                }

                die($scriptPath . ' is not executable');
            }
        }

        $this->abort('Could not find: ' . implode(',', [$optionA, $optionB, $optionC]));
    }

    private function basicCheck(): bool
    {
        foreach (
            [
                'absoluteDirOfThisScript',
                'envType',
                'ip',
                'webhookSecret',
                'webhookSecretProvided',
                'releaseScript',
            ] as $var
        ) {
            if (empty($this->{$var})) {
                $this->abort('You need to set ' . $var);

                return false;
            }
        }

        return true;
    }

    private function isIpInRange($range): bool
    {
        list($range, $netmask) = explode('/', $range, 2);
        $netmask = (int) $netmask;
        $ipLong = ip2long($this->ip);
        $rangeLong = ip2long($range);
        $wildcard = pow(2, (32 - $netmask)) - 1;
        $netmask = ~$wildcard;

        return ($ipLong & $netmask) === ($rangeLong & $netmask);
    }

    private function abort(string $reason)
    {
        if ($this->isSafeEnvironment()) {
            echo 'Aborted because of ' . $reason;
        } else {
            header('HTTP/1.1 403 Forbidden');
        }

        exit;
    }

    private function isSafeEnvironment(): bool
    {
        return true;
        $test = strtolower($this->envType);

        return 'test' === $test || 'dev' === $test;
    }

    /**
     * Get Bitbucket IP range.
     */
    private function getAtlassionIpRanges()
    {
        $atlassian = file_get_contents('https://ip-ranges.atlassian.com/');
        $ranges = json_decode($atlassian);
        // Get CIDRs (Classless Inter-Domain Routing)
        foreach ($ranges->items as $item) {
            $this->allowedCIDRS[] = $item->cidr;
        }
    }

    private function findDotEnvFile(): void
    {
        $x = 0;
        $myPath = '.env';
        $myPathAbsolute = '';
        while ($x < 30) {
            $myPathAbsolute = realpath($this->absoluteDirOfThisScript . '/' . $myPath);
            if (file_exists($myPathAbsolute)) {
                $x = 999;
            } else {
                $myPath = '../' . $myPath;
                ++$x;
            }
        }

        if (! file_exists($myPathAbsolute)) {
            throw new \InvalidArgumentException(sprintf('%s does not exist', $myPathAbsolute));
        }

        $this->path = $myPathAbsolute;
    }

    private function loadDotEnvFile(): void
    {
        if (! is_readable($this->path)) {
            throw new \RuntimeException(sprintf('%s file is not readable', $this->path));
        }

        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (0 === strpos(trim($line), '#')) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            $value = trim($value, '"');
            $value = trim($value, "'");

            if (! getenv($name)) {
                putenv(sprintf('%s=%s', $name, $value));
            }
        }
    }
}
