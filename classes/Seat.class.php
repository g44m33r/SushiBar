<?php


    class Seat {

        private $status;
        private $id;
        
        /**
         * __construct
         * Erstellt einen Sitzplatz mit einer ID
         * @param  int $seatID
         * @return void
         */
        function __construct($seatID) {
            $this->id = $seatID;
            $this->status = false;
        }

        /**
         * setOccupied
         * Sitzplatz wird belegt
         * @return void
         */
        public function setOccupied() {
            $this->status = true;
        }
        
        /**
         * setFree
         * Sitzplatz wird frei
         * @return void
         */
        public function setFree() {
            $this->status = false;
        }

        /**
         * getSeatID
         * Sitzplatzid Ausgeben
         * @return int
         */
        public function getSeatID() {
            return $this->id;
        }

        /**
         * isFreeSeat
         * Gibt zurück ob der Sitzplatz frei oder belegt ist
         * @return boolean
         */
        public function isFreeSeat() {
            if( $this->status )
                return false;
            else 
                return true;
        }

    }

?>