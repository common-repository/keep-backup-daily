<?php

namespace WPMechanic\KBFNR;

abstract class ServiceProviderAbstract {
	public function get( $id ) {
		if ( isset( $this->$id ) ) {
			return $this->$id;
		}
	}
}
