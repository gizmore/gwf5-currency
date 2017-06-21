<?php
/**
 * Builds a list of currency and conversion rates.
 * Updates them via cronnjob.
 * 
 * @author gizmore
 * @version 5.00
 */
final class Module_Currency extends GWF_Module
{
	##############
	### Module ###
	##############
	public function onLoadLanguage() { return $this->loadLanguage('lang/currency'); }
	public function getClasses() { return array('GWF_Currency'); }

	##############
	### Config ###
	##############
	public function getConfig()
	{
		return array(
			GDO_Timestamp::make('ccy_last_try')->initial(0),
			GDO_String::make('ccy_last_sync'),
			GDO_Duration::make('ccy_update_fqcy')->initial(GWF_Time::ONE_HOUR),
		);
	}
	public function cfgUpdateEnabled() { return $this->cfgUpdateFrequency() > 0; }
	public function cfgLastTry() { return $this->getConfigVar('ccy_last_try'); }
	public function cfgLastSync() { return $this->getConfigValue('ccy_last_sync'); }
	public function cfgUpdateFrequency() { return $this->getConfigValue('ccy_update_fqcy'); }
	
}
