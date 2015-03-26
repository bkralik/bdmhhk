<?php

namespace App\Presenters;

use Nette,
	App\Model,
    Nette\Application\UI,
    Nette\Mail\Message,
    Nette\Mail\SendmailMailer;


/**
 * Dokumenty presenter.
 */
class KontaktPresenter extends BasePresenter
{

	public function renderDefault()
	{
	}

    protected function createComponentEmailForm()
    {
        $form = new UI\Form;
        $form->addText('email', 'Váš email')
             ->addRule(UI\Form::EMAIL, 'Prosím, zadejte platný email.');
        
        $form->addText('name', 'Vaše jméno')
             ->setRequired('Prosím, zadejte vaše jméno.');
        
        $form->addTextArea('message', 'Zpráva')
             ->addRule(UI\Form::MIN_LENGTH, 'Zpráva je příliš krátká.', 5);
        
        $form->addSubmit('send', 'Odeslat');
        
        $form->onSuccess[] = array($this, 'emailFormSucceeded');
        $form->onValidate[] = array($this, 'validateEmailForm');
        return $form;
    }
    
    public function validateEmailForm($form)
    {
        $values = $form->getHttpData();
        
        $recaptchaSecret = $this->context->parameters["ReCaptchaSecret"];
        $recaptcha = new \ReCaptcha\ReCaptcha($recaptchaSecret);
        
        $httpRequest = $this->context->getByType('Nette\Http\Request');
        
        $resp = $recaptcha->verify($values["g-recaptcha-response"], $httpRequest->getRemoteAddress());
        if ($resp->isSuccess()) {
            // verified!
        } else {
            $errors = $resp->getErrorCodes();
            if(in_array("missing-input-response", $errors)) {
                $form->addError("Prosím, zaškrtněte políčko \"Nejsem robot\"");
            } else {
                $form->addError("Při odesílání zprávy došlo chybě, zkuste to prosím znovu.");
            }
        }
    }

    // volá se po úspěšném odeslání formuláře
    public function emailFormSucceeded(UI\Form $form, $values)
    {
        $targetMails = $this->context->parameters["KontaktyMails"];
        
        $mail = new Message;
        $mail->setFrom('bdmhhk <robot@bdmhhk.cz>')
            ->setSubject('Nová zpráva z webu bdmhhk.cz')
            ->addReplyTo($values->email)
            ->setBody($values->name." s emailem ".$values->email." posílá následující dotaz: \n\n".$values->message);
        
        foreach($targetMails as $address) {
            $mail->addTo($address);
        }

        $mailer = new SendmailMailer;
        $mailer->send($mail);

        $this->flashMessage('Zpráva byla úspěšeně odeslána.');
        $this->redirect('Kontakt:');
    }
}
