<?
/**
 * @author    Jonathan Runquist
 * @link      https://github.com/runquist/stats
 * @copyright Copyright (c) 2014 
 * @license   Use for anything
 */

class Stats {

	private $numbers = array();

    /**
	 * @param array $numbers Set of numbers for calculations.
	 **/
	function __construct($numbers=array())
	{
		$this->setNumbers($numbers);
	}

    /**
	 * Set or reset the array numbers for calculations.
	 *
	 * @param array $numbers Set of numbers for calculations.
	 * @return void
	 **/
	function setNumbers($numbers=array())
	{
		$this->numbers = $numbers;
	}

	/**
	 * Calculates exponential moving average.
	 *
	 * @param float $number Current number in the series.
	 * @param float $previousEma Previous number in the series.
	 * @param int $period 
	 * @return float
	 */
	public function getEma($number, $previousEma, $period)
	{
		if(!$previousEma) return 0;
		$emaMultiplier = 2/($period+1);
		return round(($number-$previousEma) * $emaMultiplier+$previousEma,2);
	}


	/**
	 * Calculates average.
	 *
	 * @param int $period The count of the last numbers in the sequence.
	 * @return float
	 */
	public function getAverage($period=false)
	{
		$numbers = $this->numbers;
		$count=count($numbers);
		if(!$period) $period=$count;
		if($count<1) return 0;
		if($count<$period) return 0;
		$counter = 0;
		$sum = 0;
		for($i=$count-1;$i>-1 && $counter<$period;$i--) {
			$sum+=$numbers[$i];
			$counter++;
		}
		return $sum/$counter;
	}


	/**
	 * Calculates slope.
	 *
	 * @reference http://en.wikiversity.org/wiki/Least-Squares_Method
	 * @param int $period The count of the last numbers in the sequence.
	 * @return array
	 */
	public function getSlope($period = false)
   	{
		$numbers = $this->numbers;
		if(!$period) $period=count($numbers);
		$count = 0;
		$start = count($numbers)-$period;
		if($start<0) $start=0;
		$xAvg = 0;
		$xSum = 0;
		$yAvg = 0;
		$ySum = 0;
		$xy=0;
		$xx=0;
		for($i=$start;$i<count($numbers);$i++) {
			$xSum+=$i;
			$ySum+=$numbers[$i];
			$count++;
		}
		$yAvg = $ySum/$count;
		$xAvg = $xSum/$count;
		for($i=$start;$i<count($numbers);$i++) {
			$xy+= ($i-$xAvg)*($numbers[$i]-$yAvg);
			$xx+= ($i-$xAvg)*($i-$xAvg);
		}
		$slope = ($xx==0) ? 0 : round($xy/$xx,2);
		$b = round($yAvg-$slope*$xAvg,2);
		return array('m'=>$slope,'b'=>$b);
	}

	/**
	 * Calculates standard deviation.
	 *
	 * @reference http://en.wikiversity.org/wiki/Least-Squares_Method
	 * @param int $period The count of the last numbers in the sequence.
	 * @return float
	 */
	public function getStandardDeviation($period = false)
	{
		if(!$period) $period = count($this->numbers);
		$standardDeviation = sqrt($this->getVariance($period));
		return $standardDeviation;
	}
	
	/**
	 * Calculates sample variance.
	 *
	 * @param int $period The count of the last numbers in the sequence.
	 * @return float
	 */
	public function getVariance($period = false)
	{
		if(!$period) $period = count($this->numbers);
		$numbers = $this->numbers;
		$variance = 0;
		$mean = 0;
		$a = array_slice($numbers,$period*-1,$period);
		$sum = array_sum($a);
		$count = count($a);
		if($count-1==0) return 0;
		$mean = $sum / $count;
		for ($i = 0; $i < $count; $i++)	{
			$variance += pow($a[$i] - $mean,2);
		}
		$variance = $variance / ($count - 1);
		return $variance;
	}

	/**
	 * Calculate Black Scholes.
	 * @reference http://www.espenhaug.com/black_scholes.html
	 *
	 * The risk free interest rate can be determined from the 30 year yield on treasury bills. One could
	 * use the LAST value from the following link.
	 * @link http://online.wsj.com/mdc/public/npage/9_3050.html?symb=UST30Y&page=bond
	 *
	 * Ex) $bs = $stats->blackScholes(180, 120, 3.47, '2014-12-31', 3.24);
	 *
	 * @param float $trade Last trade.
	 * @param float $strike The price at which a specific derivative contract can be exercised.
	 * @param float $riskFreeInterest Theoretical rate of return of an investment with no risk of financial loss.
	 * @param string $expiration Expiration date of the stock
	 * @param float $volatility Stock volatility
	 * @return array
	 **/
	function blackScholes($trade,$strike,$riskFreeInterest,$expiration,$volatility)
	{
		$riskFreeInterest = $riskFreeInterest/100;
		$volatility = $volatility/100;
		if(!preg_match('/(\d*)-(\d*)-(\d*)/',$expiration,$matches)) return false;
		$now = mktime(12,0,0,date('m'),date('d'),date('Y'));
		$then = mktime(12,0,0,date($matches[2]),date($matches[3]),date($matches[1]));
		$time = ($then-$now)/(3600*24*365);
		$d1 = (log($trade/$strike)+($riskFreeInterest+pow($volatility,2)/2)*$time)
			/ ($volatility*sqrt($time));
		$d2 = $d1-$volatility*sqrt($time);
		$call = $trade*self::cnd($d1)-$strike*exp(-1*$riskFreeInterest*$time)*self::cnd($d2);
		$put = $strike*exp(-1*$riskFreeInterest*$time)*self::cnd(-1*$d2)-$trade*self::cnd(-1*$d1);
		return array('d1'=>$d1,'d2'=>$d2,'call'=>$call,'put'=>$put);
	}
	
	/**
	 * Calculates cumlative normal distribution.
	 *
	 * @param int $period The count of the last numbers in the sequence.
	 * @return float
	 */
	function cnd($x) {
		$a1 = 0.319381530;
		$a2 = -0.356563782;
		$a3 = 1.781477937;
		$a4 = -1.821255978;
		$a5 = 1.330274429;
		$L = abs($x);
		$k = 1 / ( 1 + 0.2316419 * $L);
		$p = 1 - 1 /  pow(2 * pi(), 0.5) * exp( -pow($L, 2) / 2 ) * ($a1 * $k + $a2 * pow($k, 2)
			+ $a3 * pow($k, 3) + $a4 * pow($k, 4) + $a5 * pow($k, 5) );
		if ($x >= 0) return $p;
		else return 1-$p;
	}

}

