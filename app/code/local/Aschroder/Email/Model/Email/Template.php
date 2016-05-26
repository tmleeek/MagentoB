<?php

/**
 * This class wraps the Template email sending functionality
 * If enabled it will send emails using the given configuration.
 *
 * @author Ashley Schroder (aschroder.com)
 * @copyright Copyright (c) 2013 ASchroder Consulting Ltd
 */
class Aschroder_Email_Model_Email_Template extends Mage_Core_Model_Email_Template {

    /**
     * Send mail to recipient
     *
     * @param   array|string       $email        E-mail(s)
     * @param   array|string|null  $name         receiver name(s)
     * @param   array              $variables    template variables
     * @return  boolean
     **/
    public function send($email, $name = null, array $variables = array())
    {

        $_helper = Mage::helper('aschroder_email');
        // If it's not enabled, just return the parent result.
        if (!$_helper->isEnabled()) {
            $_helper->log('MageSend is not enabled, fall back to parent class');
            return parent::send($email, $name, $variables);
        }


        // As per parent class - except addition of before and after send events

        if (!$this->isValidForSend()) {
            $_helper->log('Email is not valid for sending, this is a core error that often means there\'s a problem with your email templates.');
            Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
            return false;
        }

        $emails = array_values((array)$email);
        $names = is_array($name) ? $name : (array)$name;
        $names = array_values($names);
        foreach ($emails as $key => $email) {
            if (!isset($names[$key])) {
                $names[$key] = substr($email, 0, strpos($email, '@'));
            }
        }

        $variables['email'] = reset($emails);
        $variables['name'] = reset($names);

        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($variables, true);
        $subject = $this->getProcessedTemplateSubject($variables);

        $setReturnPath = Mage::getStoreConfig(self::XML_PATH_SENDING_SET_RETURN_PATH);
        switch ($setReturnPath) {
            case 1:
                $returnPathEmail = $this->getSenderEmail();
                break;
            case 2:
                $returnPathEmail = Mage::getStoreConfig(self::XML_PATH_SENDING_RETURN_PATH_EMAIL);
                break;
            default:
                $returnPathEmail = null;
                break;
        }

        // We specifically introduce the ability to bypass the queue,
        // even when it is supposed to be used.
        if (!$_helper->isQueueBypassed() &&
            $this->hasQueue() && $this->getQueue() instanceof Mage_Core_Model_Email_Queue) {
            /** @var $emailQueue Mage_Core_Model_Email_Queue */
            $emailQueue = $this->getQueue();
            $emailQueue->setMessageBody($text);
            $emailQueue->setMessageParameters(array(
                'subject'           => $subject,
                'return_path_email' => $returnPathEmail,
                'is_plain'          => $this->isPlain(),
                'from_email'        => $this->getSenderEmail(),
                'from_name'         => $this->getSenderName(),
                'reply_to'          => $this->getMail()->getReplyTo(),
                'return_to'         => $this->getMail()->getReturnPath(),
            ))
                ->addRecipients($emails, $names, Mage_Core_Model_Email_Queue::EMAIL_TYPE_TO)
                ->addRecipients($this->_bccEmails, array(), Mage_Core_Model_Email_Queue::EMAIL_TYPE_BCC);
            $emailQueue->addMessageToQueue();

            $_helper->log('Email not sent immediately, queued for sending.');

            return true;
        }

        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

        $mail = $this->getMail();

        if ($returnPathEmail !== null) {
            $mailTransport = new Zend_Mail_Transport_Sendmail("-f".$returnPathEmail);
            Zend_Mail::setDefaultTransport($mailTransport);
        }

        $sendToEmail = 0;
        foreach ($emails as $key => $email) {
            if ($this->check_bounced_email($email)) {
                $mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
                $sendToEmail = 1;
                Mage::log('Success sending email to ' . $email . '.');
            } else {
                Mage::log('Prevent sending email to ' . $email . ', because the email address previously bounced.');
            }
        }

        if($this->isPlain()) {
            $mail->setBodyText($text);
        } else {
            $mail->setBodyHTML($text);
        }

        $mail->setSubject('=?utf-8?B?' . base64_encode($subject) . '?=');
        $mail->setFrom($this->getSenderEmail(), $this->getSenderName());

        try {

            $transport = new Varien_Object();
            Mage::dispatchEvent('aschroder_email_template_before_send', array(
                'mail' => $mail,
                'template' => $this,
                'variables' => $variables,
                'transport' => $transport
            ));

            if ($sendToEmail) {
                if ($transport->getTransport()) { // if set by an observer, use it
                    $mail->send($transport->getTransport());
                } else {
                    $mail->send();
                }
            }

            foreach ($emails as $key => $email) {
                Mage::dispatchEvent('aschroder_email_after_send', array(
                    'to' => $email,
                    'template' => $this->getTemplateId(),
                    'subject' => $this->getProcessedTemplateSubject($variables),
                    'html' => !$this->isPlain(),
                    'email_body' => $text));
            }

            $this->_mail = null;
        }
        catch (Exception $e) {
            $this->_mail = null;
            Mage::logException($e);
            return false;
        }

        return true;
    }

    public function check_bounced_email($emails)
    {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $sql = sprintf("SELECT email FROM bounced_email WHERE email = '%s' LIMIT 1", $emails);
        $result = $read->fetchAll($sql);

        return (!$result) ? true : false;
    }
}