<?php

namespace Eoko\AWSMailBundle\Service;

use Aws\Ses\SesClient;

class SesClientService
{
    /**
     * @var AwsClient
     */
    private $instance;
    
    /**
     * @var array
     */
    private $configuration;
    
    /**
     * Définit la configuration de notre client SES
     * @param array $configuration
     */
    public function setConfig(array $configuration)
    {
        $this->configuration = [
            'version' => 'latest',
            'region'  => $configuration['region'],
            'credentials' => [
                'key'    => $configuration['access_key_id'],
                'secret' => $configuration['secret_access_key']
            ]
        ];
    }
    
    /**
     * @return AwsClient
     */
    public function getInstance()
    {
        if ($this->instance === null) {
            return $this->instance = new SesClient($this->configuration);
        }
        
        return $this->instance;
    }
}