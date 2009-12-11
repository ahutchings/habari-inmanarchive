<?php

/*
 * Inman Archive Plugin
 * Usage: <?php $theme->inman_archive(); ?>
 *
 * Inspired by the archive widget on shauninman.com.
 */

class Inman_Archive extends Plugin
{
	public function info()
	{
		return array(
			'name' => 'Compact Archives',
			'url' => 'http://habariproject.org',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org',
			'version' => '0.0.1',
			'description' => 'Shows archives grouped by month.',
			'license' => 'Apache License 2.0'
		);
	}

	private function get_html() 
	{
		set_time_limit(300);

		$q = "SELECT YEAR( FROM_UNIXTIME(pubdate) ) AS year, MONTH(  FROM_UNIXTIME(pubdate)  ) AS month, COUNT( id ) AS cnt
				FROM  {posts}
				WHERE content_type = ? AND status = ?
				GROUP BY year, month
				ORDER BY pubdate DESC";
		$p[] = Post::type( 'entry' );
		$p[] = Post::status( 'published' );
		$results = DB::get_results( $q, $p );

		$archives[] = '<ul id="compact-archives">';

		if (empty($results)) {
			
			$archives[] = '<li>No Archives Found</li>';
			
		} else {

			$grouping = array();

			// group the results by year
			foreach ($results as $result) {

				if (!isset($grouping[$result->year])) {
					$grouping[$result->year] = array_fill(1, 12, 0);
				}

				$grouping[$result->year][$result->month] = $result->cnt;
			}

			// build the year lists
			$years = array_keys($grouping);
			
			for ($i = 0, $n = count($grouping); $i < $n; $i++) {
				
				$year = $years[$i];
				$archives[] = '<li><span class="year">' . $year . '</span>';
			
				$archives[] = '<ul class="months">';
					
				for ($j = 1; $j <= count($grouping[$years[$i]]); $j++) {

					// make sure the month has a 0 on the front, if it doesn't
					$month = str_pad($j, 2, 0, STR_PAD_LEFT);
					
					$month_text = date('F', mktime(0, 0, 0, $month)); 
	
					$archives[]= '<li>';
					
					if ($grouping[$years[$i]][$j] == 0) {
						$archives[] = $month;
					} else {
						$archives[]= '<a href="' . URL::get('display_entries_by_date', array('year' => $year, 'month' => $month)) . '" title="View entries in ' . $month_text . ' ' . $year . '">' . $month . '</a>';
					}
					
					$archives[]= '</li>';
				}
				$archives[] = '</ul>';
				$archives[] = '</li>';
			}
			
		}

		$archives[]= '</ul>';

		return implode('', $archives);
	}

	public function theme_inman_archive($theme)
	{
		return $this->get_html();
	}
}

?>
