<?php

class TemplateManager
{
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    private function replaceAll($replace, $text)
    {
        foreach ($replace as $search => $value) {
            (strpos($text, $search) !== false) and $text = str_replace($search, $value, $text);
        }
        return $text;
    }

    private function computeText($text, array $data)
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;

        if ($quote) {
            $site = SiteRepository::getInstance()->getById($quote->siteId);
            $destination = DestinationRepository::getInstance()->getById($quote->destinationId);
            $quote = QuoteRepository::getInstance()->getById($quote->id);

            $link = $site->url.'/'.$destination->countryName.'/quote/'.$quote->id;
            $replace = [
                '[quote:summary_html]' => Quote::renderHtml($quote),
                '[quote:summary]' => Quote::renderText($quote),
                '[quote:destination_name]' => $destination->countryName,
                '[quote:destination_link]' => $link
            ];
            $text = $this->replaceAll($replace, $text);
        }

        $_user = (isset($data['user']) and ($data['user'] instanceof User)) ? $data['user'] : $APPLICATION_CONTEXT->getCurrentUser();
        if ($_user) {
            $replace = [
                '[user:first_name]' => ucfirst(mb_strtolower($_user->firstname))
            ];
            $text = $this->replaceAll($replace, $text);
        }

        return $text;
    }
}
