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
    private $allowedCIDRS = [];
    private $webhookSecret = '';
    private $webhookSecretProvided = '';
    private $releaseScript = '';

    public function run()
    {
        if ($this->getVars()) {
            if ($this->securityChecks()) {
                shell_exec('bash ' . $this->releaseScript);
            }
        }
    }

    private function getVars(): bool
    {
        $this->findDotEnvFile();
        $this->loadDotEnvFile();
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->webhookSecret = getenv('SS_RELEASE_TOKEN');
        $this->releaseScript = getenv('SS_RELEASE_SCRIPT');
        $this->webhookSecretProvided = empty($_GET['ts']) ? '' : $_GET['ts'];
        if ($this->basicCheck()) {
            $this->getAtlassionIpRanges();

            return true;
        }

        return false;
    }

    private function basicCheck(): bool
    {
        foreach (['ip', 'webhookSecret', 'releaseScript', 'webhookSecretProvided'] as $var) {
            if (empty($this->{$var})) {
                user_error('You need to set ' . $var);
                $this->abort();

                return false;
            }
        }

        return true;
    }

    private function isIpInRange($ip, $range): bool
    {
        list($range, $netmask) = explode('/', $range, 2);
        $ipLong = ip2long($ip);
        $rangeLong = ip2long($range);
        $wildcard = pow(2, (32 - $netmask)) - 1;
        $netmask = ~$wildcard;

        return ($ipLong & $netmask) === ($rangeLong & $netmask);
    }

    private function abort()
    {
        header('HTTP/1.1 403 Forbidden');
        exit;
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

    private function securityChecks()
    {
        /*
         * (1) Primary check
         *
         * Block if for some reason we couldn't get a response from Atlassian.
         */
        if (! empty($this->allowedCIDRS)) {
            $this->abort();

            return false;
        }

        /*
         * (2) Secondary check (Additional/Optional)
         *
         * Token validation, that comes from the Bitbucket Webhook setup
         */
        if ($this->webhookSecretProvided !== $this->webhookSecret) {
            $this->abort();

            return false;
        }

        /**
         * (3) Tertiary check.
         *
         * IP validation - only allow requests for Atlassian IPs
         */
        $allow = false;
        foreach ($this->allowedCIDRS as $cidr) {
            $allow = isIpInRange($ip, $cidr);
            if ($allow) {
                break;
            }
        }

        if (! $allow) {
            $this->abort();

            return false;
        }

        return true;
    }

    private function findDotEnvFile(string $path)
    {
        $x = 0;
        $myPath = $this->path;
        while ($x < 30 && ! file_exists($myPath)) {
            $myPath .= '../' . $myPath;
            ++$x;
        }
        if (! file_exists($myPath)) {
            throw new \InvalidArgumentException(sprintf('%s does not exist', $myPath));
        }
        $this->path = $myPath;
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

            if (! array_key_exists($name, $_SERVER) && ! array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
            }
        }
    }
}
