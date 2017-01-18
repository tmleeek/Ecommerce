<?php

class Unleaded_PIMS_Helper_Import_Parts_Attributesets extends Unleaded_PIMS_Helper_Data
{
	const CATALOG_PRODUCT_ENTITY_TYPE_ID = 4;

	/* [ 'attribute_set_name' => $attributeSetId ] */
	public $attributeSets = [];

	public function getSetId($row, $sku)
	{	
		try {
			$productLineShortCode = $row['Product Line Short Code'];
			
			if (isset($this->attributeSets[$productLineShortCode]))
				return $this->attributeSets[$productLineShortCode];

			// See if we have the attribute set mapped for this product line short code
			if (!isset($this->map[$productLineShortCode])) {
				$this->error($sku . ' - ' . $productLineShortCode . ' has no attribute set');
				// Add Default to this attribute set name
				$this->attributeSets[$productLineShortCode] = $this->getDefaultAttributeSetId();
				return $this->attributeSets[$productLineShortCode];
			}

			// Get the attribute set name
			$attributeSetName = $this->map[$productLineShortCode];

			// See if it's in cache and return from cache if it is
			if (isset($this->attributeSets[$attributeSetName]))
				return $this->attributeSets[$attributeSetName];

			// Find attribute set with this name
			$attributeSetCollection = Mage::getModel('eav/entity_attribute_set')
										->getCollection()
										->addFieldToFilter('attribute_set_name', $attributeSetName)
										->addFieldToFilter('entity_type_id', self::CATALOG_PRODUCT_ENTITY_TYPE_ID);

			foreach ($attributeSetCollection as $attributeSet) {
				// Add it to cache and return from cache
				$this->attributeSets[$attributeSetName] = $attributeSet->getAttributeSetId();
				return $this->attributeSets[$attributeSetName];
			}
		} catch (Exception $e) {
			$this->error($e->getMessage());
			return $this->getDefaultAttributeSetId();
		}
	}

	protected function getDefaultAttributeSetId()
	{
		$entityTypeId     = Mage::getModel('eav/entity')
				                ->setType('catalog_product')
				                ->getTypeId();
		$attributeSetName = 'Default';
		$attributeSets    = Mage::getModel('eav/entity_attribute_set')
			                    ->getCollection()
			                    ->setEntityTypeFilter($entityTypeId)
			                    ->addFieldToFilter('attribute_set_name', $attributeSetName);
		foreach ($attributeSets as $attributeSet)
			return $attributeSet->getAttributeSetId();
	}

	public $map = [
		// Product Line Name            => Attribute Set
		"SHADOW WIPER COWL"              => "Default",
		"COMMERCIAL STEEL HEADACHE RACK" => "Truck",
		"TAILGATE PROTECTORS"            => "Truck",
		"TAILGATE SEAL"                  => "Truck",
		"BULL BAR ACCESSORIES"           => "Bars",
		"BULL BAR LIGHT"                 => "Bars",
		"BULL BAR W-LED LIGHT BAR-BLACK" => "Bars",
		"BULL BAR W-LED LIGHT BAR-SS"    => "Bars",
		"CARGO BAR"                      => "Default",
		"CARGO CARRIER ACCESSORIES"      => "Default",
		"CARGO CARRIERS"                 => "Default",
		"HITCH RACK"                     => "Default",
		"HITCH STEP"                     => "Default",
		"RAMPS"                          => "Default",
		"ROOF RACKS"                     => "Default",
		"SOFT CARGO PACKS"               => "Default",
		"TIE DOWNS AND HOOKS"            => "Default",
		"MATTE FINISH COMBO KIT"         => "Default",
		"TEXTURED FINISH COMBO KIT"      => "Default",
		"8' CBT COMPLETE DIAMOND PLATE"  => "Boxes",
		"8' CBT DIAMOND PLATE TONNEAU"   => "Boxes",
		"8' CBT SIDE BOX"                => "Boxes",
		"8' CBT SOFT TONNEAU"            => "Boxes",
		"CBT COMPLETE - TONNEAU-D-PLATE" => "Boxes",
		"SHORT BED CBT SIDE BOX"         => "Boxes",
		"SHORT BED CBT SOFT TONNEAU"     => "Boxes",
		"SHRT BED CBT DMD-PLATE TONNEAU" => "Boxes",
		"CHROME DOOR HANDLE COVERS-2DR"  => "Default",
		"CHROME DOOR HANDLE COVERS-4DR"  => "Default",
		"CHROME DOOR LEVER COVERS-2DR"   => "Default",
		"CHROME DOOR LEVER COVERS-4DR"   => "Default",
		"CHROME MIRROR COVERS"           => "Default",
		"CHROME TAILGATE HANDLE COVERS"  => "Default",
		"FUEL DOOR COVER"                => "Default",
		"ACCESSORIES for FENDER FLARES"  => "Fender Flares",
		"EX-EXTRAWIDE STYLE 2PC SMOOTH"  => "Fender Flares",
		"EX-EXTRAWIDE STYLE 2PC TEXTURD" => "Fender Flares",
		"EX-EXTRAWIDE STYLE 4PC STANDRD" => "Fender Flares",
		"EX-EXTRAWIDE STYLE 4PC TEXTURD" => "Fender Flares",
		"RX-RIVET STYLE 2PC SMOOTH"      => "Fender Flares",
		"RX-RIVET STYLE 2PC TEXTURED"    => "Fender Flares",
		"RX-RIVET STYLE 4PC SMOOTH"      => "Fender Flares",
		"RX-RIVET STYLE 4PC TEXTURED"    => "Fender Flares",
		"SX-SPORT STYLE 2PC SMOOTH"      => "Fender Flares",
		"SX-SPORT STYLE 2PC TEXTURED"    => "Fender Flares",
		"SX-SPORT STYLE 4PC SMOOTH"      => "Fender Flares",
		"SX-SPORT STYLE 4PC TEXTURED"    => "Fender Flares",
		"CARGO LOGIC FOR TRUCKS"         => "Default",
		"CARGO LOGIC LOKS"               => "Default",
		"CARGO-LOGIC"                    => "Default",
		"CARGO-LOGIC TOTES"              => "Default",
		"CATCH-ALL 2ND AND 3RD ROW"      => "Default",
		"CATCH-ALL CENTER HUMP"          => "Default",
		"CATCH-ALL FRONT 2-PIECE SET"    => "Default",
		"CATCH-ALL PLUS FRONT ONE PIECE" => "Default",
		"CATCH-ALL REAR CARGO"           => "Default",
		"CATCH-ALL SECOND ROW"           => "Default",
		"CATCH-ALL XTREME 2ND & 3RD ROW" => "Default",
		"CATCH-ALL XTREME CENTER HUMP"   => "Default",
		"CATCH-ALL XTREME FRNT 2-PC SET" => "Default",
		"CATCH-ALL XTREME PLUS FRT-1 PC" => "Default",
		"CATCH-ALL XTREME REAR CARGO"    => "Default",
		"CATCH-ALL XTREME SECOND ROW"    => "Default",
		"CATCH-IT CARPET FRONT"          => "Default",
		"CATCH-IT CARPET REAR"           => "Default",
		"CATCH-IT FLOORMAT-FRONT ONLY"   => "Default",
		"CATCH-IT FLOORMATS-REAR ONLY"   => "Default",
		"PROLINE - AFTERMARKET"          => "Default",
		"UNIVERSAL CARGO LOGIC LARGE"    => "Default",
		"UNIVERSAL CARGO LOGIC MEDIUM"   => "Default",
		"UNIVERSAL CARGO LOGIC SMALL"    => "Default",
		"GRILLES - FRAMED PERIMETER"     => "Default",
		"GRILLES - ORIGINAL BAR"         => "Default",
		"AEROSKIN ACRYLIC HOODPROTECTOR" => "Default",
		"AEROSKIN CHROME HOOD PROTECTOR" => "Default",
		"AEROSKIN II TEXTURED BLACK"     => "Default",
		"AEROSKIN II TEXTURED MATTE BLK" => "Default",
		"AEROSKIN MATTE BLACK"           => "Default",
		"AEROSKIN TEXTURED BLACK"        => "Default",
		"BUGFLECTOR"                     => "Default",
		"BUGFLECTOR DELUXE 3PC"          => "Default",
		"BUGFLECTOR II"                  => "Default",
		"CARFLECTOR"                     => "Default",
		"CHROME HOOD SHIELD"             => "Default",
		"FENDER PROTECTOR"               => "Default",
		"HOODFLECTOR"                    => "Default",
		"INTERCEPTOR"                    => "Default",
		"STEPSHIELD - 2PC BLACK"         => "Default",
		"STEPSHIELD - 3PC BLACK"         => "Default",
		"STEPSHIELD - 4PC BLACK"         => "Default",
		"FX-JEEP FLAT STYLE 4PC SMOOTH"  => "Fender Flares",
		"FX-JEEP FLAT STYLE 4PC TEXTRD"  => "Fender Flares",
		"RX-JEEP RIVET STYLE 4PC SMOOTH" => "Fender Flares",
		"RX-JEEP RIVET STYLE 4PC TEXTRD" => "Fender Flares",
		"HEADLIGHT COVER - LARGE SMOKE"  => "Default",
		"HEADLIGHT COVER - SMOKE"        => "Default",
		"PROJEKTORZ - 2PC"               => "Default",
		"PROJEKTORZ - 4PC"               => "Default",
		"SLOTS"                          => "Default",
		"TAIL SHADE - LARGER SIZE"       => "Default",
		"TAIL SHADE 2"                   => "Default",
		"TAILSHADES"                     => "Default",
		"ACCESSORIES"                    => "Liquid storage",
		"ALUMINUM TANKS & COMBO TANK"    => "Liquid storage",
		"STEEL LIQUID STORAGE TANKS"     => "Liquid storage",
		"MAXI-CHROME"                    => "Default",
		"PATRIOT PACKAGE"                => "Default",
		"SEAMLESS-BLACKOUT"              => "Default",
		"ASSEMBLY KITS"                  => "Default",
		"OTHER"                          => "Default",
		"3 In ROUND BENT STAINLES STEEL" => "Bars",
		"3 In ROUND BENT STEEL"          => "Bars",
		"4 In OVAL CURVED STAINLESS STL" => "Bars",
		"4 In OVAL CURVED STEEL"         => "Bars",
		"4 In OVAL STRAIGHT SS"          => "Bars",
		"4 In OVAL STRAIGHT STEEL"       => "Bars",
		"4 In ROUND BENT STAINLESS STL"  => "Bars",
		"4 In ROUND BENT STEEL"          => "Bars",
		"5 In CURVED OVAL SS"            => "Bars",
		"5 In OVAL CURVED STEEL"         => "Bars",
		"5 In OVAL STRAIGHT SS"          => "Bars",
		"5 In OVAL STRAIGHT STEEL"       => "Bars",
		"5 In OVAL WTW STAINLESS STEEL"  => "Bars",
		"5 In OVAL WTW STEEL"            => "Bars",
		"5INCH-OVAL BENT"                => "Bars",
		"5INCH-OVAL BLACK"               => "Bars",
		"5INCH-OVAL CHROME"              => "Bars",
		"6 In OVAL STRAIGHT BLACK"       => "Bars",
		"6 In OVAL STRAIGHT SS"          => "Bars",
		"6INCH-OVAL BLACK"               => "Bars",
		"6INCH-OVAL CHROME"              => "Bars",
		"6INCH-OVAL STRAIGHT BLACK"      => "Bars",
		"6INCH-OVAL STRAIGHT CHROME"     => "Bars",
		"LATITUDE BLACK"                 => "Bars",
		"LATITUDE STAINLESS STEEL"       => "Bars",
		"LATITUDE STEP PADS"             => "Bars",
		"ROCK RAIL"                      => "Bars",
		"ROCK RAIL LONG STEP"            => "Bars",
		"ROCK RAIL SHORT STEP"           => "Bars",
		"STEP RAILS MULTI-FIT BOARDS"    => "Bars",
		"TERRAIN HX STEP"                => "Bars",
		"RHINO LINER PANEL GUARD-LARGE"  => "Default",
		"RHINO LINER PANEL GUARD-SMALL"  => "Default",
		"RHINO LININGS ROCKER GUARDS-LG" => "Default",
		"RHINO LININGS ROCKER GUARDS-SM" => "Default",
		"CROSSROADS RUNNING BOARD 70IN"  => "Default",
		"CROSSROADS RUNNING BOARD 80IN"  => "Default",
		"CROSSROADS RUNNING BOARD 87IN"  => "Default",
		"CROSSROADS RUNNING BRD KIT 70"  => "Default",
		"CROSSROADS RUNNING BRD KIT 80"  => "Default",
		"CROSSROADS RUNNING BRD KIT 87"  => "Default",
		"FACTORY STYLE MULTI-FIT BOARDS" => "Default",
		"LUND EZ BRACKET KIT"            => "Default",
		"MUD FLAPS"                      => "Default",
		"OE STYLE NO DRILL BRACKET"      => "Default",
		"SUMMIT RIDGE BLACK"             => "Default",
		"SUMMIT RIDGE CHROME"            => "Default",
		"SUMMIT RIDGE STRAIGHT BLACK"    => "Default",
		"SUMMIT RIDGE STRAIGHT CHROME"   => "Default",
		"TRAILBACK RUNNING BOARDS"       => "Default",
		"TRAILRUNNER DIAMOND MULTI-FIT"  => "Default",
		"TRAILRUNNER EXTRUDED MULTI-FIT" => "Default",
		"TUBE STEP BRACKETS"             => "Default",
		"HOOD SCOOPS"                    => "Default",
		"ALUM FOAM FILLED LID TRUCK BOX" => "Boxes",
		"ALUM INDUST.SIZE UNDER BODIES"  => "Boxes",
		"ALUM SGL LID HD-28 CROSS BOX"   => "Boxes",
		"ALUM TOP MOUNTS L WING"         => "Boxes",
		"ALUM TRAILER TONGUE"            => "Boxes",
		"ALUMINUM ECONOMY CROSS BOXES"   => "Boxes",
		"ALUMINUM FLUSH MOUNT BOXES"     => "Boxes",
		"ALUMINUM GULL WING CROSS BOXES" => "Boxes",
		"ALUMINUM INFRAME TRUCK BOXID"   => "Boxes",
		"ALUMINUM SINGLE LID CROSS BOX"  => "Boxes",
		"ALUMINUM SPECIALTY BOXES"       => "Boxes",
		"ALUMINUM UNDER BODIES"          => "Boxes",
		"CHALLENGER SPECIALTY TOOL BOXE" => "Boxes",
		"CHALLENGER TOOL BOXES"          => "Boxes",
		"CHALLENGER TOOL BOXES - BLACK"  => "Boxes",
		"COM PRO ALUM CROSS BED BOX"     => "Boxes",
		"COM PRO ALUM SIDE BIN BOX"      => "Boxes",
		"COMMERCIAL PRO STEEL SIDE BINS" => "Boxes",
		"COMMERCIAL STEEL CROSS BOXES"   => "Boxes",
		"CONTENDER TOOL BOXES"           => "Boxes",
		"OPP BLACK STORAGE BOXES"        => "Boxes",
		"RHINO LINED ITEMS"              => "Boxes",
		"SEAL-TITE DIAMOND TOOL BOXES"   => "Boxes",
		"SEAL-TITE POLISHED TOOL BOXES"  => "Boxes",
		"STEEL CROSS BOXES"              => "Boxes",
		"STEEL JOB SITE BOXES & CHEST"   => "Boxes",
		"STEEL PRO CROSS BOXES"          => "Boxes",
		"STEEL SPECIALTY BOXES"          => "Boxes",
		"STEEL TOP MOUNT"                => "Boxes",
		"STEEL UNDERBODY BOXES"          => "Boxes",
		"TOOL BOX ACCESSORIES"           => "Boxes",
		"ULTIMA TOOL BOXES"              => "Boxes",
		"ULTIMA TOOL BOXES - BLACK"      => "Boxes",
		"UNDERBODY ASSEMBLY TRAYS"       => "Boxes",
		"GENESIS ELITE ROLL UP TONNEAU"  => "Trucks",
		"GENESIS ELITE SEAL&PEEL TONNEA" => "Trucks",
		"GENESIS ELITE SNAP TONNEAU"     => "Trucks",
		"GENESIS ELITE TRI-FOLD TONNEAU" => "Trucks",
		"GENESIS ROLL UP TONNEAU"        => "Trucks",
		"GENESIS SEAL & PEEL TONNEAU"    => "Trucks",
		"GENESIS SNAP TONNEAU"           => "Trucks",
		"GENESIS TRI-FOLD TONNEAU"       => "Trucks",
		"HARD FOLD TONNEAU LUND"         => "Trucks",
		"REVELATION TONNEAU"             => "Trucks",
		"AEROSHADE"                      => "Default",
		"AEROVISOR - 2PC FRONT"          => "Default",
		"CHROME VENTVISOR - 2PC"         => "Default",
		"CHROME VENTVISOR - 4PC"         => "Default",
		"IN-CHANNEL VENTVISOR 2PC"       => "Default",
		"IN-CHANNEL VENTVISOR 4PC"       => "Default",
		"LOW PROFILE MATTE BLACK 2 PC"   => "Default",
		"LOW PROFILE MATTE BLACK 4 PC"   => "Default",
		"LOW PROFILE TEXTURED BLACK 2PC" => "Default",
		"LOW PROFILE TEXTURED BLACK 4PC" => "Default",
		"LOW-PRFILE VNTVSR 4PC CHRM TRM" => "Default",
		"LOW-PRFILE VNTVSR 6PC CHRM TRM" => "Default",
		"LOW-PROFILE VENTVISR 2PC SMOKE" => "Default",
		"LOW-PROFILE VENTVISR 4PC SMOKE" => "Default",
		"LOW-PROFILE VENTVISR 6PC SMOKE" => "Default",
		"OVER THE ROAD VENTVISOR"        => "Default",
		"REAR VENTVISOR"                 => "Default",
		"SUNFLECTOR"                     => "Default",
		"SUNFLECTORS FOR TRUCKS"         => "Default",
		"TAPE-ONZ - 4PC CHROME"          => "Default",
		"VENTSHADE - 2PC BLACK"          => "Default",
		"VENTSHADE - 2PC STAINLESS"      => "Default",
		"VENTSHADE - 2PC XWIDE"          => "Default",
		"VENTSHADE - 4PC BLACK"          => "Default",
		"VENTSHADE - 4PC STAINLESS"      => "Default",
		"VENTVISOR 2PC"                  => "Default",
		"VENTVISOR 4PC"                  => "Default",
		"VENTVISOR ELITE - 2 PC"         => "Default",
		"VENTVISOR ELITE - 4PC"          => "Default",
		"WINDFLECTOR - CLASSIC"          => "Default",
		"WINDFLECTOR - POP-OUT"          => "Default",
	];
}