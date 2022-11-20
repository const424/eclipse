<?php
namespace Const424\Eclipse;

use PHPMailer\PHPMailer\PHPMailer;

class Email extends PHPMailer
{
	protected array $config;
	
	public function __construct()
	{
		$this->config = config('email');
		
		$this->isHTML(true);
		$this->isSMTP();
		$this->setFrom($this->config['from_address'], $this->config['from_name']);
		$this->SMTPAuth = true;
		$this->SMTPSecure = $this->config['secure'];
		$this->Port = $this->config['port'];
		$this->Host = $this->config['host'];
		$this->Username = $this->config['username'];
		$this->Password = $this->config['password'];
	}
	
	public function setPriority(int $priority)
	{
		$this->Priority = $priority;
	}
	
	public function setSubject(string $subject)
	{
		$this->Subject = ($this->config['subject_prefix'] ? "{$this->config['subject_prefix']} " : '') . $subject;
	}
	
	public function setBody(string $body)
	{
		$this->Body = $body;
	}
	
	public function setAltBody(string $body)
	{
		$this->AltBody = $body;
	}
}