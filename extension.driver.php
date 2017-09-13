<?php

class extension_email_sendgrid extends Extension
{
    public function uninstall()
    {
        /**
         * preferences are defined in the email gateway class,
         * but removing upon uninstallation must be handled here;
         */
        Symphony::Configuration()->remove('email_sendgrid');
        Symphony::Configuration()->write();
        return true;
    }
}
