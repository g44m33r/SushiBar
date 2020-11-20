<?php

    class Group {

        private $groupSize;
        private $seats;
        
        /**
         * __construct
         *
         * @param  mixed $amount
         * @param  mixed $seats
         * @return void
         */
        function __construct($amount, $seats) {
            $this->groupSize = $amount;
            $this->seats = $seats;
        }
        
        /**
         * getSeats
         * Gibt die Sitzplätze die von der Gruppe belegt sind zurück
         * @return Seat[]
         */
        public function getSeats() {
            return $this->seats;
        }
        
        /**
         * getGroupSize
         * Gibt die Größe der Gruppe zurück
         * @return int
         */
        public function getGroupSize() {
            return $this->groupSize;
        }

    }

?>