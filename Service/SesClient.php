<?php

namespace Eoko\AWSMailBundle\Service;

use Aws\AwsClient;

class SesClient
{
    /**
     * @var AwsClient
     */
    private $instance;
    
    /**
     * Définit la configuration de notre client SES
     * @param array $configuration
     */
    public function setConfiguration(array $configuration)
    {
        $this->instance = new AwsClient($configuration);
    }
    
    /**
     * @return AwsClient
     */
    public function getInstance()
    {
        return $this->instance;
    }
    
}