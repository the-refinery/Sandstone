<?php

class LoginPage extends BasePage
{
	public function __construct()
	{
		parent::__construct();

		$this->_isLoginRequired = false;
		$this->_allowedRoleIDs = Array();
	}

	public function AUTH301_Processor($EventParameters)
	{
		if (is_set($_SERVER['PHP_AUTH_USER']))
		{
			//Attempt a login
			$tempUser = new User();

			$loginTest = $tempUser->Login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

			if ($loginTest == true)
			{
				$session = Application::Session();

				if (is_set($session['TargetRoutingString']))
				{
					$targetURL = $session['TargetRoutingString'];

					Application::ClearSessionVariable('TargetRoutingString');

					Application::Redirect($targetURL);
				}
				else
				{
					Application::Redirect(Routing::BuildURLbyRule('default'));
				}
			}
			else
			{
				header('WWW-Authenticate: Basic Realm="Sandstone Test"');
				header("HTTP/1.1 401 Unauthorized");
			}
 		}
		else
		{
			header('WWW-Authenticate: Basic Realm="Sandstone Test"');
			header("HTTP/1.1 401 Unauthorized");
		}
	}

	public function HTM_Processor($EventParameters)
	{
		parent::HTM_Processor($EventParameters);

		if (Application::CurrentUser() &&
			(Application::CurrentUser()->IsInRole(new Role(2)) || Application::CurrentUser()->IsInRole(new Role(3))) &&
			Application::License()->IsValid
			)
		{
			Application::Redirect(Routing::BuildURLbyRule('default'));
		}

		if (is_set($this->_template->NotificationMessage) == false)
		{
			$this->_template->NotificationMessage = "Please login to continue.";
		}
	}

	/*** FORM PROCESSORS ***/

	protected function LoginForm_Processor($EventParameters)
	{
		// Account Test
		if ($this->LoginForm->AccountName->Value)
		{
			$accountName = $this->LoginForm->AccountName->Value;
		}
		else
		{
			$accountName = $EventParameters['subdomain'];
		}

		$accountCheck = Application::SelectAccount($accountName);


		$license = Application::License();

		if ($license->IsCancelled)
		{
			Application::Redirect(Routing::BuildURLbyRule('cancelled'));
		}

		if ($accountCheck == true)
		{
			$tempUser = new User();

			$loginTest = $tempUser->Login($this->LoginForm->Username->Value, $this->LoginForm->Password->Value);

			if ($loginTest == true)
			{
				$session = Application::Session();

				if (is_set($session['TargetRoutingString']))
				{
					$this->LoginForm->RedirectTarget = $session['TargetRoutingString'];
					Application::ClearSessionVariable('TargetRoutingString');
				}
				else
				{
					$this->LoginForm->RedirectTarget = Routing::BuildURLbyRule('default');
				}
			}
			else
			{
				$this->SetNotificationMessage("Invalid Username or Password");
			}
		}
		else
		{
			$this->SetNotificationMessage("Invalid Account");
		}

		return true;
	}

	/*** CONTROLS ***/

	protected function BuildControlArray($EventParameters)
	{
		$this->LoginForm = new PageForm($EventParameters);

		$this->LoginForm->AccountName = new TextboxControl();
		$this->LoginForm->AccountName->LabelText = "Account Name:";

		$this->LoginForm->Username = new TextboxControl();
		$this->LoginForm->Username->LabelText = "Username:";

		$this->LoginForm->Password = new PasswordControl();
		$this->LoginForm->Password->LabelText = "Password:";

		$this->LoginForm->Submit = new SubmitButtonControl();
		$this->LoginForm->Submit->LabelText = "Login";

		parent::BuildControlArray($EventParameters);
	}

	protected function LoadControlData($EventParameters)
	{
		if ($EventParameters['subdomain'])
		{
			$this->LoginForm->AccountName->DefaultValue = $EventParameters['subdomain'];
			$this->LoginForm->AccountName->IsRendered = false;
		}
	}
}

?>