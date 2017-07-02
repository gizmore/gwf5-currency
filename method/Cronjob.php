<?php
final class Currency_Cronjob extends GWF_MethodCronjob
{
	public function run()
	{
		if (Module_Currency::instance()->cfgUpdateEnabled())
		{
			$this->trySyncCurrencies();
		}
	}
	
	public function trySyncCurrencies()
	{
		$module = Module_Currency::instance();
		
		$frequency = $module->cfgUpdateFrequency();
		$lastTry = round($module->cfgLastTry() / $frequency);
		$nowTry = round(time() / $frequency);
		if ($lastTry !== $nowTry)
		{
			$module->saveConfigValue('ccy_last_try', time());
			$this->syncCurrencies($module);
		}
	}

	public function syncCurrencies(Module_Currency $module)
	{
		$this->log("Requesting ECB exchange rates");
		$xml = simplexml_load_file("http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml");
		$nowStamp = $xml->Cube->Cube["time"]->__toString();
		$lastStamp = $module->cfgLastSync();
		if ($nowStamp !== $lastStamp)
		{
			$this->log("Got new EZB exchange rates");
			foreach($xml->Cube->Cube->Cube as $rate)
			{
				$this->syncCurrency($module, $rate['currency']->__toString(), $rate['rate']->__toString());
			}
			$module->saveConfigVar('ccy_last_sync', $nowStamp);
		}
	}
	
	private static function syncCurrency(Module_Currency $module, string $iso, string $rate)
	{
		if (!($currency = GWF_Currency::getByISO($iso)))
		{
			$currency = GWF_Currency::blank(['ccy_iso'=>$iso, 'ccy_ratio'=>$rate, 'ccy_symbol'=>$iso]);
		}
		
		if ($currency->isSyncAutomated())
		{
			$currency->setVar('ccy_ratio', $rate);
			$currency->save();
		}
	}
	
}
