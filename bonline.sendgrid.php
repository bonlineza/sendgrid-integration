<?php
require 'vendor/autoload.php';

/*
 *
 * Response Codes
 *  404 -> Missing parameters
 *
 *
 *
 *
 */
class BOnline_SendGrid {
  protected $send_grid_access_key = '';
  protected $default_template = 'c29af03a-64b3-4dea-9a80-f543e3db6133';
  protected $default_subject = 'Hello WOrld';
  protected $default_from = 'rob@hicarbyecar.com';

  function __construct() {
    $secrets_str = file_get_contents('./secrets.json');
    $secrets_json = json_decode($secrets_str);

    if ($secrets_json === NULL) {
      throw new Exception('Error Reading Secrets file');
    } else if (!isset($secrets_json['SENDGRID_API_KEY'])) {
      throw new Exception('Sendgrid Api Key missing from secrets');
    } else {
      $this->send_grid_access_key = $secrets_json['SENDGRID_API_KEY'];

    }
  }

  public function send(
    $body = '',
    $recip = '',
    $template = null,
    $subject = null,
    $from = null
) {
    // error handling
    if ($template === null) $template = $this->default_template;
    if ($subject === null) $subject = $this->default_subject;
    if ($from === null) $from = $this->default_from;
    if (
      empty($body)
      || empty($template)
      || empty($recip)
      || empty($from)
      || empty($subject)
    ) {
      return (object) [
        'code' => 404,
        'msg' => 'The following parameters are missing :: ' . (empty($body) ? ' body ' : '' ) .
          (empty($template) ? ' template ' : '') . '' . 
          (empty($recip) ? ' recipients ' : '') . '' .
          (empty($from) ? ' from ' : '') .
          (empty(subject) ? ' subject ' : ''),
      ];
    }

    // okay now we can get onto the real work here
    if (is_array($recip)) {
      // send same email to multiple people
    } else {
      // only single email
      $res = $this->sg_send($from, $subject, $recip, $body, $template);
    }
    return $res;
  }

  protected function sg_send($from, $subject, $recip, $content, $template) {
    // set up send grid variables
    $sg_from = new SendGrid\Email(null, $from);
    $sg_subject = $subject;
    $sg_to = new SendGrid\Email(null, $from);
    $sg_content = new SendGrid\Content("text/html", $content);
    $sg_mail = new SendGrid\Mail($sg_from, $sg_subject, $sg_to, $sg_content);
    $sg_mail->setTemplateId($template);
    print $this->send_grid_access_key;
    $sg = new \SendGrid($this->send_grid_access_key);
      
    try {
      $response = $sg->client->mail()->send()->post($sg_mail);
      return $response;
    } catch (Exception $e) {
      return (object) [
        'code' => 500,
        'msg' => $e->getMessage(),
      ];
    }
  }
}

$s = new BOnline_SendGrid();
echo '<pre>';
var_dump(
  $s->send('hello world', 'robert.crous21@gmail.com')
);
