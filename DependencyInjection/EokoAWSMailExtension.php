<?php

namespace Eoko\AWSMailBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Eoko\AWSMailBundle\Exception\ConfigurationException;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class EokoAWSMailExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        $awsConf = [];
        if ($config['use_environment']) {
            $awsConf = [
                'access_key_id'     => getenv('AWS_ACCESS_KEY_ID'),
                'secret_access_key' => getenv('AWS_SECRET_ACCESS_KEY'),
                'default_region'    => getenv('AWS_DEFAULT_REGION')
            ];
            
            if ($awsConf['access_key_id'] === false ||
                    $awsConf['secret_access_key'] === false ||
                    $awsConf['default_region'] === false) {
                throw new ConfigurationException("access_key_id, secret_access_key or default_region not defined in EokoAwsMailBundle");
            }
        } else {
            $awsConf = $config['configuration'];
            
            if (empty($awsConf['access_key_id']) ||
                    empty($awsConf['secret_access_key']) ||
                    empty($awsConf['default_region'])) {
                throw new ConfigurationException("access_key_id, secret_access_key or default_region not defined in EokoAwsMailBundle");
            }
        }
        
        $container->register('eoko_aws_ses_client', 'Eoko\AWSMailBundle\Service\SesClient')
                  ->addMethodCall('setConfiguration', [ $awsConf ]);
        
    }
}
