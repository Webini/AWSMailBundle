<?php

namespace Eoko\AWSMailBundle\Service;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;
use DateTime;
use Doctrine\ORM\EntityManager;
use GS\MailBundle\Entity\Mail;
use GS\ToolBundle\Manager\AbstractFlushManager;
use GS\MailBundle\Repository\MailRepository;
use Eoko\AWSMailBundle\Service\SesClientService;
use Aws\AwsClient;

/**
 * @author nico
 */
class MailService 
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
     * @var AwsClient
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
                                SesClient $mailer, $locale) 
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
     * Prépare le message pour un expédition immédiate
     * @param string $recipient
     * @param string $recipientEmail
     * @param string $sender
     * @param string $senderEmail
     * @param string $subject
     * @param string $locale
     * @todo Rajouter le html2text
     * @return \Swift_Message
     */
    protected function prepareSwiftMesssage(Mail $mail) 
    {
        return Swift_Message::newInstance()
                            ->setSubject($mail->getSubject())
                            ->setFrom([ $mail->getSender() => $mail->getSenderName() ])
                            ->setTo([ $mail->getRecipient() => $mail->getRecipientName() ])
                            ->setBody($mail->getMessage(), 'text/html');
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
        $swiftMail        = $this->prepareSwiftMesssage($mail);
        $failedRecipients = '';
        
        $this->mailer->send($swiftMail, $failedRecipients);
        
        $mail->setShippedAt(new DateTime());
        
        if (!empty($failedRecipients)) {
            $mail->setFailed(implode(', ', $failedRecipients));
        }
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
