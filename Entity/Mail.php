<?php

namespace Eoko\AWSMailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Mail
 *
 * @ORM\Table(schema="app", name="Mail")
 * ORM\Entity(repositoryClass="GS\MailBundle\Repository\MailRepository")
 */
class Mail
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Email(checkMX = true, checkHost = true)
     * @Assert\Length(max = 255)	
     * @ORM\Column(name="recipient", type="string", length=255)
     */
    private $recipient;
    
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(max = 255)
     * @ORM\Column(name="recipient_name", type="string", length=255)
     */
    private $recipientName;

    /**
     * @var string
     *
     * @ORM\Column(name="sender", type="string", length=255)
     */
    private $sender;
    
    /**
     * @var string
     * @ORM\Column(name="sender_name", type="string", length=255)
     */
    private $senderName;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(max = 255)
     * @ORM\Column(name="subject", type="string", length=255)
     */
    private $subject;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(max = 255)
     * @ORM\Column(name="message", type="text")
     */
    private $message;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="shipping_date", type="datetime")
     */
    private $shippingDate;

    /**
     * @var string
     *
     * @ORM\Column(name="failed", type="string", length=255, nullable=true)
     */
    private $failed;

    /**
     * @var boolean
     * @ORM\Column(name="shipped_at", type="datetime", nullable=true)
     */
    private $shippedAt;

    public function __construct() 
    {
        $this->shippingDate = new DateTime();
    }
    
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Définit lorsque le mail a été envoyé
     * @param DateTime $date
     * @return \GS\MailBundle\Entity\Mail
     */
    public function setShippedAt(DateTime $date = null)
    {
        $this->shippedAt = $date;
        return $this;
    }
    
    /**
     * @return \DateTime
     */
    public function getShippedAt()
    {
        return $this->shippedAt;
    }
    
    /**
     * @return boolean
     */
    public function isShipped()
    {
        return ($this->shippedAt !== null);
    }

    /**
     * @return string
     */
    public function getRecipientName()
    {
        return $this->recipientName;
    }
    
    /**
     * @param string $name
     * @return \GS\MailBundle\Entity\Mail
     */
    public function setRecipientName($name)
    {
        $this->recipientName = $name;
        return $this;
    }
    
    /**
     * Set recipient
     *
     * @param string $recipient
     *
     * @return Mail
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Get recipient
     *
     * @return string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @return string
     */
    public function getSenderName()
    {
        return $this->senderName;
    }
    
    /**
     * @param string $name
     * @return \GS\MailBundle\Entity\Mail
     */
    public function setSenderName($name)
    {
        $this->senderName = $name;
        return $this;
    }
    
    /**
     * Set sender
     *
     * @param string $sender
     *
     * @return Mail
     */
    public function setSender($sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Set subject
     *
     * @param string $subject
     *
     * @return Mail
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return Mail
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set shippingDate
     *
     * @param \DateTime $shippingDate
     *
     * @return Mail
     */
    public function setShippingDate(DateTime $shippingDate)
    {
        $this->shippingDate = $shippingDate;

        return $this;
    }

    /**
     * Get shippingDate
     *
     * @return \DateTime
     */
    public function getShippingDate()
    {
        return $this->shippingDate;
    }

    /**
     * Set failed
     *
     * @param string $failed
     *
     * @return Mail
     */
    public function setFailed($failed)
    {
        $this->failed = $failed;

        return $this;
    }

    /**
     * Get failed
     *
     * @return string
     */
    public function getFailed()
    {
        return $this->failed;
    }
}

