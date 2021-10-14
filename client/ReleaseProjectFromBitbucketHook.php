<?php

$obj = new ReleaseProjectFromBitbucketHook();
$obj->run();


class ReleaseProjectFromBitbucketHook 
{

    private $allow = false;
    private $ip = '';
    private $allowedCIDRS = [];
    private $webhookSecret = '';
    private $webhookSecretProvided = '';
    private $releaseScript = '';

      
    public function run()
    {
        $this->getVars();
        if($this->securityChecks()) {
            shell_exec('bash '.$this->releaseScript);
        }
    }
  
    private function getVars()
    {
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->webhookSecret = getenv('SS_RELEASE_TOKEN');
        $this->releaseScript = getenv('SS_RELEASE_SCRIPT');
        $this->webhookSecretProvided = empty($_GET['ts']) ? '' : $_GET['ts'];
        $this->getAtlassionIpRanges();
    }
    
    private function isIpInRange($ip, $range) {
        list($range, $netmask) = explode('/', $range, 2);
        $ipLong = ip2long($ip);
        $rangeLong = ip2long($range);
        $wildcard = pow(2, (32 - $netmask)) - 1;
        $netmask = ~ $wildcard;
        
        return (($ipLong & $netmask) == ($rangeLong & $netmask));
    }
    
    private function abort() {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }
    
    /**
    * Get Bitbucket IP range
    */
    private function getAtlassionIpRanges() 
    {
        $atlassian = file_get_contents('https://ip-ranges.atlassian.com/');
        $ranges = json_decode($atlassian);
        /**
        * Get CIDRs (Classless Inter-Domain Routing)
        */
        foreach($ranges->items as $item) {
            $allowedCIDRS[] = $item->cidr;
        }
    }

    private function securityChecks() 
    {
        
        /**
        * (1) Primary check
        * 
        * Block if for some reason we couldn't get a response from Atlassian.
        */
        if (!empty($this->allowedCIDRS)) {
            $this->abort();
            return false;
        }

        /**
        * (2) Secondary check (Additional/Optional)
        * 
        * Token validation, that comes from the Bitbucket Webhook setup
        */
        if($this->webhookSecretProvided !== $this->webhookSecret) {
            $this->abort();
            return false;
        }

        /**
        * (3) Tertiary check
        * 
        * IP validation - only allow requests for Atlassian IPs
        */
       $allow = false;
        foreach ($allowedCIDRS as $cidr) {
            $allow = isIpInRange($ip, $cidr);
            if ($allow) {
                break;
            }
        }

        if (!$allow) {
            $this->abort();
            return false;
        }  
        return true;      
    }


    
}
