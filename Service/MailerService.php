<?php

namespace Eoko\AWSMailBundle\Service;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;
use DateTime;
use Doctrine\ORM\EntityManager;
use Eoko\AWSMailBundle\Entity\Mail;
use Eoko\AWSMailBundle\Service\SesClientService;
use Aws\Ses\SesClient;

/**
 * @author nico
 */
class MailerService 
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    
    /**
     * @var TwigEngine
     */
    private $twig;

    /**
     * @var SesClientService
     */
    private $mailer;
    
    /**
     * @var array
     */
    private $config;
    
    /**
     * @var string
     */
    private $locale;
    
    /**
     * @param TranslatorInterface $translator
     * @param TwigEngine $twig
     * @param SesClient $mailer
     * @param string $locale
     */
    public function __construct(TranslatorInterface $translator, TwigEngine $twig, 
                                SesClientService $mailer, $locale) 
    {
        $this->translator = $translator;
        $this->twig       = $twig;
        $this->mailer     = $mailer;
        $this->locale     = $locale;
    }
    
    /**
     * Injection de la config du bundle
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }
    
    /**
     * Redéfinit la locale par defaut du translator
     * @return \GS\MailBundle\Service\MailService
     */
    private function restoreTranslatorLocale()
    {
        $this->translator->setLocale($this->locale);
        return $this;
    }

    /**
     * Expédition immédiate
     * @param string $recipient
     * @param string $recipientEmail
     * @param string $sender
     * @param string $senderEmail
     * @param string $subject
     * @param string $locale
     * @return mixed
     */
    protected function sendMessage(Mail $mail) 
    {
        return $this->mailer->getInstance()->sendEmail([
            'Destination' => [
                'ToAddresses' => [
                    $mail->getSender(),
                ]
            ],
            'Message' => [
                'Body' => [
                    'Html' => [
                        'Charset' => 'UTF-8',
                        'Data'    => $mail->getMessage()
                    ]
                ],
                'Subject' => [
                    'Charset' => 'UTF-8',
                    'Data'    => $mail->getSubject()
                ]
            ],
            'Source' => $mail->getSender(),
            'ReplyToAddresses' => [ $mail->getSender() ]
        ]);
    }
    
    /**
     * Créer l'entity mail
     * @return Mail
     */
    public function createNewMail()
    {
        $mail = new Mail();
        $mail->setSender($this->config['default_sender_email'])
             ->setSenderName($this->config['default_sender_name']);
        
        return $mail;
    }
    
    /**
     * Va expédier ce mail puis le supprimer
     * @param Mail $mail
     */
    public function sendOne(Mail $mail)
    {
        $response = $this->sendMessage($mail);
        
        $mail->setFailed($response);
        $mail->setShippedAt(new DateTime());
    }
    
    /**
     * Créer et remplis l'entity mail
     * @param string $recipient
     * @param string $recipientEmail
     * @param string $subject
     * @param string $message
     * @param string $shipping
     * @param string $sender
     * @param string $senderEmail
     * @return \GS\MailBundle\Entity\Mail
     */
    protected function prepareMailEntity($recipient, $recipientEmail, $subject, 
                                            $message, $shipping = null, $sender = null, 
                                            $senderEmail = null)
    {
        $mail = $this->createNewMail();
        
        $mail->setRecipient($recipientEmail)
             ->setRecipientName($recipient)
             ->setMessage($message);
        
        if (!empty($senderEmail)) {
            $mail->setSender($senderEmail);
        }
        
        if (!empty($sender)) {
            $mail->setSenderName($sender);
        }
        
        if (is_array($subject)) {
            $mail->setSubject($this->translator->trans($subject[0], $subject[1]));
        } else {
            $mail->setSubject($this->translator->trans($subject));
        }
        
        if (!empty($shipping)) {
            $mail->setShippingDate($shipping);
        }
        
        return $mail;
    }
    
    /**
     * @param string $recipient
     * @param string $recipientEmail
     * @param string|array $subject Si array le premier parametre correspond a la chaine, et le deuxieme aux variables
     * @param string $message
     * @param string $locale
     * @param DateTime $shipping Heure d'éxpédition choisie
     * @param string $sender
     * @param string $senderEmail
     * @return \GS\MailBundle\Entity\Mail
     */
    public function addMail($recipient, $recipientEmail, $subject, $message, 
                                $locale = null, $shipping = null, $sender = null, 
                                $senderEmail = null)
    {
        if (!empty($locale)) {
            $this->translator->setLocale($locale);
        }
        
        $mail = $this->prepareMailEntity($recipient, $recipientEmail, $subject, $message, $shipping, $sender, $senderEmail);
        
        $this->restoreTranslatorLocale();
        
        return $mail;
    }
    
    /**
     * @param string $recipient
     * @param string $recipientEmail
     * @param string|array $subject Si array le premier parametre correspond a la chaine, et le deuxieme aux variables
     * @param string $template Nom du template
     * @param array $parameters Paramètres du template
     * @param string $locale
     * @param DateTime $shipping Heure d'éxpédition choisie
     * @param string $sender
     * @param string $senderEmail
     * @return \GS\MailBundle\Entity\Mail
     */
    public function addTemplatedMail($recipient, $recipientEmail, $subject, 
                                        $template, $parameters = [], $locale = null,
                                        $shipping = null, $sender = null, $senderEmail = null)
    {
        if (!empty($locale)) {
            $this->translator->setLocale($locale);
        }
        
        $message = $this->twig->render($template, $parameters);
        $mail = $this->prepareMailEntity($recipient, $recipientEmail, $subject, $message, $shipping, $sender, $senderEmail);
        
        $this->restoreTranslatorLocale();
        
        return $mail;
    }
}
