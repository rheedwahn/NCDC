<?php

namespace App\Services;

use Stringy\StaticStringy;

class ScrapNcdcWebsite extends BaseService
{
    const NCDC_WEBSITE = 'https://covid19.ncdc.gov.ng/';

    const SEARCH_CONTENT = ['<td>', '</td>', '<p>', '</p>', '<b>', '</b>', '<tr>', '</tr>', '<th>', '</th>'];

    public function run()
    {
        return $this->keyStateWithStats();
    }

    protected function keyStateWithStats()
    {
        $keyed_cases = [];
        $cases = $this->removeHtmlTableCharacters();
        foreach ($cases as $case) {
            $exploded = explode('   ', $case);
            $keyed_cases[] = [
                $exploded[0] => [
                    'confirmed_cases' => $exploded[1],
                    'active_cases' => $exploded[2],
                    'recovered_cases' => $exploded[3],
                    'dead_cases' => $exploded[4]
                ]
            ];
        }
        return $keyed_cases;
    }

    protected function removeHtmlTableCharacters()
    {
        $removed_trs = [];
        $table_case_focus = $this->tableCaseFocus();
        $string_content = StaticStringy::between($table_case_focus, '<tbody>', '</tbody>');
        $exploded_content = explode('</tr> <tr>', $string_content);
        foreach ($exploded_content as $content) {
            $removed_trs[] = trim(str_replace(self::SEARCH_CONTENT, '', $content));
        }
        array_splice($removed_trs, 0, 1);
        return $removed_trs;
    }

    protected function curlSite()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, self::NCDC_WEBSITE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    protected function tableCaseFocus()
    {
        $html = $this->curlSite();
        $start_point = strpos($html, '<table id="custom3" class="table table-responsive">');
        $end_point = strpos($html, '</table>', $start_point);
        $length = $end_point-$start_point;
        $html = substr($html, $start_point, $length);
        return $this->removeTrailingCharacters($html);
    }
}
