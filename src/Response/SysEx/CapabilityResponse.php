<?php

namespace Carica\Firmata\Response\SysEx {

  use Carica\Firmata;

  /**
   * The $pins property reports the supportes modes for each value. The key of the array for
   * each pin is the mode, the element contains the maxmimum value.
   *
   * array(pin_number => array(mode => maximum))
   *
   * @property array $pins
   */
  class CapabilityResponse extends Firmata\Response {

    /**
     * @var array
     */
    private $_pins = array();

    /**
     * @param array $bytes
     */
    public function __construct(array $bytes) {
      parent::__construct(Firmata\Board::CAPABILITY_RESPONSE, $bytes);
      $length = count($bytes);
      $pin = 0;
      $i = 0;
      while ($i < $length) {
        if ($bytes[$i] == 0x7F) {
          /*
           * pin report end, check if it was added to the array, if not add an empty element
           */
          if (!isset($this->_pins[$pin])) {
            $this->_pins[$pin] = array();
          }
          ++$pin;
          ++$i;
          continue;
        } else {
          $mode = $bytes[$i];
          /*
           * The resolution of the pins is reported as a bit count
           */
          $maximum = pow(2, (int)$bytes[$i + 1]) - 1;
          if ($mode == Firmata\Board::PIN_MODE_SERVO) {
            /*
             * Servo reports an resolution of 14 bits (maxmimum value 16383), but
             * uses mostly degrees to set the position, so we use 1 as a full circle of
             * 360 degrees
             */
            $this->_pins[$pin][$mode] = 359;
          } elseif ($mode == Firmata\Board::PIN_MODE_ANALOG && $maximum == 0) {
            /**
             * Use 10bit as a default for analog pins
             */
            $this->_pins[$pin][$mode] = 1023;
          } else {
            $this->_pins[$pin][$mode] = $maximum;
          }
        }
        $i += 2;
      }
    }

    /**
     * @param string $name
     * @return array|int
     */
    public function __get($name) {
      switch ($name) {
      case 'pins' :
        return $this->_pins;
      }
      return parent::__get($name);
    }
  }
}