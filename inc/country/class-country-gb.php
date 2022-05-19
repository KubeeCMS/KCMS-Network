<?php
/**
 * Country Class for United Kingdom (GB).
 *
 * State/province count: 247
 * City count: 3871
 * City count per state/province:
 * - ENG: 2919 cities
 * - SCT: 530 cities
 * - WLS: 302 cities
 * - NYK: 120 cities
 *
 * @package WP_Ultimo\Country
 * @since 2.0.11
 */

namespace WP_Ultimo\Country;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Country Class for United Kingdom (GB).
 *
 * @since 2.0.11
 * @internal last-generated in 2022-05
 * @generated class generated by our build scripts, do not change!
 *
 * @property-read string $code
 * @property-read string $currency
 * @property-read int $phone_code
 */
class Country_GB extends Country {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * General country attributes.
	 *
	 * This might be useful, might be not.
	 * In case of doubt, keep it.
	 *
	 * @since 2.0.11
	 * @var array
	 */
	protected $attributes = array(
		'country_code' => 'GB',
		'currency'     => 'GBP',
		'phone_code'   => 44,
	);

	/**
	 * The type of nomenclature used to refer to the country sub-divisions.
	 *
	 * @since 2.0.11
	 * @var string
	 */
	protected $state_type = 'unknown';

	/**
	 * Return the country name.
	 *
	 * @since 2.0.11
	 * @return string
	 */
	public function get_name() {

		return __('United Kingdom', 'wp-ultimo');

	} // end get_name;

	/**
	 * Returns the list of states for GB.
	 *
	 * @since 2.0.11
	 * @return array The list of state/provinces for the country.
	 */
	protected function states() {

		return array(
			'ABE' => __('Aberdeen', 'wp-ultimo'),
			'ABD' => __('Aberdeenshire', 'wp-ultimo'),
			'ANS' => __('Angus', 'wp-ultimo'),
			'ANT' => __('Antrim', 'wp-ultimo'),
			'ANN' => __('Antrim and Newtownabbey', 'wp-ultimo'),
			'ARD' => __('Ards', 'wp-ultimo'),
			'AND' => __('Ards and North Down', 'wp-ultimo'),
			'AGB' => __('Argyll and Bute', 'wp-ultimo'),
			'ARM' => __('Armagh City and District Council', 'wp-ultimo'),
			'ABC' => __('Armagh, Banbridge and Craigavon', 'wp-ultimo'),
			'SH-AC' => __('Ascension Island', 'wp-ultimo'),
			'BLA' => __('Ballymena Borough', 'wp-ultimo'),
			'BLY' => __('Ballymoney', 'wp-ultimo'),
			'BNB' => __('Banbridge', 'wp-ultimo'),
			'BNS' => __('Barnsley', 'wp-ultimo'),
			'BAS' => __('Bath and North East Somerset', 'wp-ultimo'),
			'BDF' => __('Bedford', 'wp-ultimo'),
			'BFS' => __('Belfast district', 'wp-ultimo'),
			'BIR' => __('Birmingham', 'wp-ultimo'),
			'BBD' => __('Blackburn with Darwen', 'wp-ultimo'),
			'BPL' => __('Blackpool', 'wp-ultimo'),
			'BGW' => __('Blaenau Gwent County Borough', 'wp-ultimo'),
			'BOL' => __('Bolton', 'wp-ultimo'),
			'BMH' => __('Bournemouth', 'wp-ultimo'),
			'BRC' => __('Bracknell Forest', 'wp-ultimo'),
			'BRD' => __('Bradford', 'wp-ultimo'),
			'BGE' => __('Bridgend County Borough', 'wp-ultimo'),
			'BNH' => __('Brighton and Hove', 'wp-ultimo'),
			'BKM' => __('Buckinghamshire', 'wp-ultimo'),
			'BUR' => __('Bury', 'wp-ultimo'),
			'CAY' => __('Caerphilly County Borough', 'wp-ultimo'),
			'CLD' => __('Calderdale', 'wp-ultimo'),
			'CAM' => __('Cambridgeshire', 'wp-ultimo'),
			'CMN' => __('Carmarthenshire', 'wp-ultimo'),
			'CKF' => __('Carrickfergus Borough Council', 'wp-ultimo'),
			'CSR' => __('Castlereagh', 'wp-ultimo'),
			'CCG' => __('Causeway Coast and Glens', 'wp-ultimo'),
			'CBF' => __('Central Bedfordshire', 'wp-ultimo'),
			'CGN' => __('Ceredigion', 'wp-ultimo'),
			'CHE' => __('Cheshire East', 'wp-ultimo'),
			'CHW' => __('Cheshire West and Chester', 'wp-ultimo'),
			'CRF' => __('City and County of Cardiff', 'wp-ultimo'),
			'SWA' => __('City and County of Swansea', 'wp-ultimo'),
			'BST' => __('City of Bristol', 'wp-ultimo'),
			'DER' => __('City of Derby', 'wp-ultimo'),
			'KHL' => __('City of Kingston upon Hull', 'wp-ultimo'),
			'LCE' => __('City of Leicester', 'wp-ultimo'),
			'LND' => __('City of London', 'wp-ultimo'),
			'NGM' => __('City of Nottingham', 'wp-ultimo'),
			'PTE' => __('City of Peterborough', 'wp-ultimo'),
			'PLY' => __('City of Plymouth', 'wp-ultimo'),
			'POR' => __('City of Portsmouth', 'wp-ultimo'),
			'STH' => __('City of Southampton', 'wp-ultimo'),
			'STE' => __('City of Stoke-on-Trent', 'wp-ultimo'),
			'SND' => __('City of Sunderland', 'wp-ultimo'),
			'WSM' => __('City of Westminster', 'wp-ultimo'),
			'WLV' => __('City of Wolverhampton', 'wp-ultimo'),
			'YOR' => __('City of York', 'wp-ultimo'),
			'CLK' => __('Clackmannanshire', 'wp-ultimo'),
			'CLR' => __('Coleraine Borough Council', 'wp-ultimo'),
			'CWY' => __('Conwy County Borough', 'wp-ultimo'),
			'CKT' => __('Cookstown District Council', 'wp-ultimo'),
			'CON' => __('Cornwall', 'wp-ultimo'),
			'DUR' => __('County Durham', 'wp-ultimo'),
			'COV' => __('Coventry', 'wp-ultimo'),
			'CGV' => __('Craigavon Borough Council', 'wp-ultimo'),
			'CMA' => __('Cumbria', 'wp-ultimo'),
			'DAL' => __('Darlington', 'wp-ultimo'),
			'DEN' => __('Denbighshire', 'wp-ultimo'),
			'DBY' => __('Derbyshire', 'wp-ultimo'),
			'DRY' => __('Derry City Council', 'wp-ultimo'),
			'DRS' => __('Derry City and Strabane', 'wp-ultimo'),
			'DEV' => __('Devon', 'wp-ultimo'),
			'DNC' => __('Doncaster', 'wp-ultimo'),
			'DOR' => __('Dorset', 'wp-ultimo'),
			'DOW' => __('Down District Council', 'wp-ultimo'),
			'DUD' => __('Dudley', 'wp-ultimo'),
			'DGY' => __('Dumfries and Galloway', 'wp-ultimo'),
			'DND' => __('Dundee', 'wp-ultimo'),
			'DGN' => __('Dungannon and South Tyrone Borough Council', 'wp-ultimo'),
			'EAY' => __('East Ayrshire', 'wp-ultimo'),
			'EDU' => __('East Dunbartonshire', 'wp-ultimo'),
			'ELN' => __('East Lothian', 'wp-ultimo'),
			'ERW' => __('East Renfrewshire', 'wp-ultimo'),
			'ERY' => __('East Riding of Yorkshire', 'wp-ultimo'),
			'ESX' => __('East Sussex', 'wp-ultimo'),
			'EDH' => __('Edinburgh', 'wp-ultimo'),
			'ENG' => __('England', 'wp-ultimo'),
			'ESS' => __('Essex', 'wp-ultimo'),
			'FAL' => __('Falkirk', 'wp-ultimo'),
			'FER' => __('Fermanagh District Council', 'wp-ultimo'),
			'FMO' => __('Fermanagh and Omagh', 'wp-ultimo'),
			'FIF' => __('Fife', 'wp-ultimo'),
			'FLN' => __('Flintshire', 'wp-ultimo'),
			'GAT' => __('Gateshead', 'wp-ultimo'),
			'GLG' => __('Glasgow', 'wp-ultimo'),
			'GLS' => __('Gloucestershire', 'wp-ultimo'),
			'GWN' => __('Gwynedd', 'wp-ultimo'),
			'HAL' => __('Halton', 'wp-ultimo'),
			'HAM' => __('Hampshire', 'wp-ultimo'),
			'HPL' => __('Hartlepool', 'wp-ultimo'),
			'HEF' => __('Herefordshire', 'wp-ultimo'),
			'HRT' => __('Hertfordshire', 'wp-ultimo'),
			'HLD' => __('Highland', 'wp-ultimo'),
			'IVC' => __('Inverclyde', 'wp-ultimo'),
			'IOW' => __('Isle of Wight', 'wp-ultimo'),
			'IOS' => __('Isles of Scilly', 'wp-ultimo'),
			'KEN' => __('Kent', 'wp-ultimo'),
			'KIR' => __('Kirklees', 'wp-ultimo'),
			'KWL' => __('Knowsley', 'wp-ultimo'),
			'LAN' => __('Lancashire', 'wp-ultimo'),
			'LRN' => __('Larne Borough Council', 'wp-ultimo'),
			'LDS' => __('Leeds', 'wp-ultimo'),
			'LEC' => __('Leicestershire', 'wp-ultimo'),
			'LMV' => __('Limavady Borough Council', 'wp-ultimo'),
			'LIN' => __('Lincolnshire', 'wp-ultimo'),
			'LSB' => __('Lisburn City Council', 'wp-ultimo'),
			'LBC' => __('Lisburn and Castlereagh', 'wp-ultimo'),
			'LIV' => __('Liverpool', 'wp-ultimo'),
			'BDG' => __('London Borough of Barking and Dagenham', 'wp-ultimo'),
			'BNE' => __('London Borough of Barnet', 'wp-ultimo'),
			'BEX' => __('London Borough of Bexley', 'wp-ultimo'),
			'BEN' => __('London Borough of Brent', 'wp-ultimo'),
			'BRY' => __('London Borough of Bromley', 'wp-ultimo'),
			'CMD' => __('London Borough of Camden', 'wp-ultimo'),
			'CRY' => __('London Borough of Croydon', 'wp-ultimo'),
			'EAL' => __('London Borough of Ealing', 'wp-ultimo'),
			'ENF' => __('London Borough of Enfield', 'wp-ultimo'),
			'HCK' => __('London Borough of Hackney', 'wp-ultimo'),
			'HMF' => __('London Borough of Hammersmith and Fulham', 'wp-ultimo'),
			'HRY' => __('London Borough of Haringey', 'wp-ultimo'),
			'HRW' => __('London Borough of Harrow', 'wp-ultimo'),
			'HAV' => __('London Borough of Havering', 'wp-ultimo'),
			'HIL' => __('London Borough of Hillingdon', 'wp-ultimo'),
			'HNS' => __('London Borough of Hounslow', 'wp-ultimo'),
			'ISL' => __('London Borough of Islington', 'wp-ultimo'),
			'LBH' => __('London Borough of Lambeth', 'wp-ultimo'),
			'LEW' => __('London Borough of Lewisham', 'wp-ultimo'),
			'MRT' => __('London Borough of Merton', 'wp-ultimo'),
			'NWM' => __('London Borough of Newham', 'wp-ultimo'),
			'RDB' => __('London Borough of Redbridge', 'wp-ultimo'),
			'RIC' => __('London Borough of Richmond upon Thames', 'wp-ultimo'),
			'SWK' => __('London Borough of Southwark', 'wp-ultimo'),
			'STN' => __('London Borough of Sutton', 'wp-ultimo'),
			'TWH' => __('London Borough of Tower Hamlets', 'wp-ultimo'),
			'WFT' => __('London Borough of Waltham Forest', 'wp-ultimo'),
			'WND' => __('London Borough of Wandsworth', 'wp-ultimo'),
			'MFT' => __('Magherafelt District Council', 'wp-ultimo'),
			'MAN' => __('Manchester', 'wp-ultimo'),
			'MDW' => __('Medway', 'wp-ultimo'),
			'MTY' => __('Merthyr Tydfil County Borough', 'wp-ultimo'),
			'WGN' => __('Metropolitan Borough of Wigan', 'wp-ultimo'),
			'MUL' => __('Mid Ulster', 'wp-ultimo'),
			'MEA' => __('Mid and East Antrim', 'wp-ultimo'),
			'MDB' => __('Middlesbrough', 'wp-ultimo'),
			'MLN' => __('Midlothian', 'wp-ultimo'),
			'MIK' => __('Milton Keynes', 'wp-ultimo'),
			'MON' => __('Monmouthshire', 'wp-ultimo'),
			'MRY' => __('Moray', 'wp-ultimo'),
			'MYL' => __('Moyle District Council', 'wp-ultimo'),
			'NTL' => __('Neath Port Talbot County Borough', 'wp-ultimo'),
			'NET' => __('Newcastle upon Tyne', 'wp-ultimo'),
			'NWP' => __('Newport', 'wp-ultimo'),
			'NYM' => __('Newry and Mourne District Council', 'wp-ultimo'),
			'NMD' => __('Newry, Mourne and Down', 'wp-ultimo'),
			'NTA' => __('Newtownabbey Borough Council', 'wp-ultimo'),
			'NFK' => __('Norfolk', 'wp-ultimo'),
			'NAY' => __('North Ayrshire', 'wp-ultimo'),
			'NDN' => __('North Down Borough Council', 'wp-ultimo'),
			'NEL' => __('North East Lincolnshire', 'wp-ultimo'),
			'NLK' => __('North Lanarkshire', 'wp-ultimo'),
			'NLN' => __('North Lincolnshire', 'wp-ultimo'),
			'NSM' => __('North Somerset', 'wp-ultimo'),
			'NTY' => __('North Tyneside', 'wp-ultimo'),
			'NYK' => __('North Yorkshire', 'wp-ultimo'),
			'NTH' => __('Northamptonshire', 'wp-ultimo'),
			'NIR' => __('Northern Ireland', 'wp-ultimo'),
			'NBL' => __('Northumberland', 'wp-ultimo'),
			'NTT' => __('Nottinghamshire', 'wp-ultimo'),
			'OLD' => __('Oldham', 'wp-ultimo'),
			'OMH' => __('Omagh District Council', 'wp-ultimo'),
			'ORK' => __('Orkney Islands', 'wp-ultimo'),
			'ELS' => __('Outer Hebrides', 'wp-ultimo'),
			'OXF' => __('Oxfordshire', 'wp-ultimo'),
			'PEM' => __('Pembrokeshire', 'wp-ultimo'),
			'PKN' => __('Perth and Kinross', 'wp-ultimo'),
			'POL' => __('Poole', 'wp-ultimo'),
			'POW' => __('Powys', 'wp-ultimo'),
			'RDG' => __('Reading', 'wp-ultimo'),
			'RCC' => __('Redcar and Cleveland', 'wp-ultimo'),
			'RFW' => __('Renfrewshire', 'wp-ultimo'),
			'RCT' => __('Rhondda Cynon Taf', 'wp-ultimo'),
			'RCH' => __('Rochdale', 'wp-ultimo'),
			'ROT' => __('Rotherham', 'wp-ultimo'),
			'GRE' => __('Royal Borough of Greenwich', 'wp-ultimo'),
			'KEC' => __('Royal Borough of Kensington and Chelsea', 'wp-ultimo'),
			'KTT' => __('Royal Borough of Kingston upon Thames', 'wp-ultimo'),
			'RUT' => __('Rutland', 'wp-ultimo'),
			'SH-HL' => __('Saint Helena', 'wp-ultimo'),
			'SLF' => __('Salford', 'wp-ultimo'),
			'SAW' => __('Sandwell', 'wp-ultimo'),
			'SCT' => __('Scotland', 'wp-ultimo'),
			'SCB' => __('Scottish Borders', 'wp-ultimo'),
			'SFT' => __('Sefton', 'wp-ultimo'),
			'SHF' => __('Sheffield', 'wp-ultimo'),
			'ZET' => __('Shetland Islands', 'wp-ultimo'),
			'SHR' => __('Shropshire', 'wp-ultimo'),
			'SLG' => __('Slough', 'wp-ultimo'),
			'SOL' => __('Solihull', 'wp-ultimo'),
			'SOM' => __('Somerset', 'wp-ultimo'),
			'SAY' => __('South Ayrshire', 'wp-ultimo'),
			'SGC' => __('South Gloucestershire', 'wp-ultimo'),
			'SLK' => __('South Lanarkshire', 'wp-ultimo'),
			'STY' => __('South Tyneside', 'wp-ultimo'),
			'SOS' => __('Southend-on-Sea', 'wp-ultimo'),
			'SHN' => __('St Helens', 'wp-ultimo'),
			'STS' => __('Staffordshire', 'wp-ultimo'),
			'STG' => __('Stirling', 'wp-ultimo'),
			'SKP' => __('Stockport', 'wp-ultimo'),
			'STT' => __('Stockton-on-Tees', 'wp-ultimo'),
			'STB' => __('Strabane District Council', 'wp-ultimo'),
			'SFK' => __('Suffolk', 'wp-ultimo'),
			'SRY' => __('Surrey', 'wp-ultimo'),
			'SWD' => __('Swindon', 'wp-ultimo'),
			'TAM' => __('Tameside', 'wp-ultimo'),
			'TFW' => __('Telford and Wrekin', 'wp-ultimo'),
			'THR' => __('Thurrock', 'wp-ultimo'),
			'TOB' => __('Torbay', 'wp-ultimo'),
			'TOF' => __('Torfaen', 'wp-ultimo'),
			'TRF' => __('Trafford', 'wp-ultimo'),
			'UKM' => __('United Kingdom', 'wp-ultimo'),
			'VGL' => __('Vale of Glamorgan', 'wp-ultimo'),
			'WKF' => __('Wakefield', 'wp-ultimo'),
			'WLS' => __('Wales', 'wp-ultimo'),
			'WLL' => __('Walsall', 'wp-ultimo'),
			'WRT' => __('Warrington', 'wp-ultimo'),
			'WAR' => __('Warwickshire', 'wp-ultimo'),
			'WBK' => __('West Berkshire', 'wp-ultimo'),
			'WDU' => __('West Dunbartonshire', 'wp-ultimo'),
			'WLN' => __('West Lothian', 'wp-ultimo'),
			'WSX' => __('West Sussex', 'wp-ultimo'),
			'WIL' => __('Wiltshire', 'wp-ultimo'),
			'WNM' => __('Windsor and Maidenhead', 'wp-ultimo'),
			'WRL' => __('Wirral', 'wp-ultimo'),
			'WOK' => __('Wokingham', 'wp-ultimo'),
			'WOR' => __('Worcestershire', 'wp-ultimo'),
			'WRX' => __('Wrexham County Borough', 'wp-ultimo'),
		);

	} // end states;

} // end class Country_GB;
