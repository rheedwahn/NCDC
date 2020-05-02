<?php

namespace App\Services;

use Stringy\StaticStringy;

class ScrapNcdcWebsite extends BaseService
{
    const NCDC_WEBSITE = 'https://covid19.ncdc.gov.ng/';

    const SEARCH_CONTENT = ['<td>', '</td>', '<tr>', '</tr>'];

    public function run()
    {
        return $this->keyStateWithStats();
    }

    /**
     * @return array
     * NB
     * $cc stands for confirmed cases
     * $ac stands for active cases
     * $rc stands for recovered cases
     * $dc stands for dead cases
     */
    protected function keyStateWithStats()
    {
        $keyed_cases = [];
        $total_confirmed_cases = 0;
        $total_active_cases = 0;
        $total_recovered_cases = 0;
        $total_dead_cases = 0;
        $cases = $this->removeHtmlTableCharacters();
        foreach ($cases as $case) {
            $exploded = explode('  ', $case);
            $keyed_cases[] = [
                $exploded[0] => [
                    'confirmed_cases' => $cc = (integer) str_replace(',', '', $exploded[1]),
                    'active_cases' => $ac = (integer)str_replace(',', '', $exploded[2]),
                    'recovered_cases' => $rc = (integer)str_replace(',', '', $exploded[3]),
                    'dead_cases' => $dc = (integer)str_replace(',', '', $exploded[4])
                ]
            ];

            $total_confirmed_cases = $total_confirmed_cases + $cc;
            $total_active_cases = $total_active_cases + $ac;
            $total_recovered_cases = $total_recovered_cases + $rc;
            $total_dead_cases = $total_dead_cases + $dc;
        }
        $keyed_cases[] = [
            'total' => [
                'total_confirmed_cases' => $total_confirmed_cases,
                'total_active_cases' => $total_active_cases,
                'total_recovered_cases' => $total_recovered_cases,
                'total_dead_cases' => $total_dead_cases
            ]
        ];
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
        $start_point = strpos($html, '<table id="custom1">');
        $end_point = strpos($html, '</table>', $start_point);
        $length = $end_point-$start_point;
        $html = substr($html, $start_point, $length);
        return $this->removeTrailingCharacters($html);
    }
}
