<?php

NameSpace::Using("Sandstone.Application");
NameSpace::Using("Sandstone.Knowledgebase");

class KnowledgebaseViewPage extends BasePage
{

	protected $_selectedArticle;

	protected function Generic_PreProcessor(&$EventParameters)
	{

		if (is_set($EventParameters['seopagename']))
		{
			$seoPage = new SEOpage();
			$seoPage->LoadByName($EventParameters['seopagename']);

			if ($seoPage->IsLoaded)
			{
				if ($seoPage->AssociatedEntityType = "KnowledgebaseArticle")
				{
					$this->_selectedArticle = new KnowledgebaseArticle($seoPage->AssociatedEntityID);

					if ($this->_selectedArticle->IsLoaded && $this->_selectedArticle->IsPublished)
					{
						$this->_template->Article = $this->_selectedArticle;
					}
					else
					{
						//Either no article found for that ID, or it's not published
						$this->SetResponseCode(404, $EventParameters);
					}
				}
				else
				{
					//Not a KB article SEO page
					$this->SetResponseCode(404, $EventParameters);
				}
			}
			else
			{
				//No SEO page by that name
				$this->SetResponseCode(404, $EventParameters);
			}
		}
		else
		{
			//No SEO page passed
			$this->SetResponseCode(404, $EventParameters);
		}

	}

}
?>