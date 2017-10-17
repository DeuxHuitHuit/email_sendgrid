<?php

require_once(EXTENSIONS . '/email_sendgrid/vendor/autoload.php');

class SendGridGateway extends EmailGateway
{
    const SETTINGS_GROUP = 'email_sendgrid';
    
    public function about()
    {
        return array(
            'name' => 'SendGrid',
        );
    }

    public function send()
    {
        if (empty($this->_sender_email_address)) {
            $from_email = Symphony::Configuration()->get('from_address', self::SETTINGS_GROUP);
            $this->setSenderEmailAddress($from_email);
        }
        
        if (empty($this->_sender_name)) {
            $from_name = Symphony::Configuration()->get('from_name', self::SETTINGS_GROUP);
            $this->setSenderName($from_name);
        }
        
        $this->validate();
        
        // build from address
        $from = new SendGrid\Email($from_name, $from_email);
        
        // only set HTML body if it exists
        if (!empty($this->_text_html)) {
            $content = new SendGrid\Content("text/html", $this->_text_html);
        } else {
            $content = new SendGrid\Content("text/plain", $this->_text_plain);
        }
        
        $apiKey = Symphony::Configuration()->get('api_key', self::SETTINGS_GROUP);
        $sg = new \SendGrid($apiKey);
        
        // Send individual emails
        foreach ($this->_recipients as $name => $address) {
            if (is_numeric($name)) {
                $to = new SendGrid\Email('', $address);
            }
            else {
                $to = new SendGrid\Email($name, $address);
            }
            $mail = new SendGrid\Mail($from, $this->_subject, $to, $content);
            $response = $sg->client->mail()->send()->post($mail);
            // handle bad responses (202 == Continue)
            if ($response->statusCode() !== 202) {
                throw new EmailGatewayException(
                    $response->body() ?: 'Unknown Error'
                );
            }
        }
        
        return true;
    }

    /**
     * The preferences to add to the preferences pane in the admin area.
     *
     * @return XMLElement
     */
    public function getPreferencesPane()
    {
        $group = new XMLElement('fieldset');
        $group->setAttribute('class', 'settings condensed pickable');
        $group->setAttribute('id', 'sendgrid');

        $div = new XMLElement('div');
        $div->setAttribute('class', 'columns three');

        $label = Widget::Label(__('From Name'));
        $label->setAttribute('class', 'column');
        $label->appendChild(Widget::Input('settings[email_sendgrid][from_name]', Symphony::Configuration()->get('from_name', self::SETTINGS_GROUP)));
        $div->appendChild($label);

        $label = Widget::Label(__('From Address'));
        $label->setAttribute('class', 'column');
        $label->appendChild(Widget::Input('settings[email_sendgrid][from_address]', Symphony::Configuration()->get('from_address', self::SETTINGS_GROUP)));
        $div->appendChild($label);

        $label = Widget::Label(__('API Key'));
        $label->setAttribute('class', 'column');
        $label->appendChild(Widget::Input('settings[email_sendgrid][api_key]', Symphony::Configuration()->get('api_key', self::SETTINGS_GROUP)));
        $div->appendChild($label);

        $group->appendChild($div);

        return $group;
    }
}
