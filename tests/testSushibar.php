<?php 

include "../classes/Sushibar.class.php";

use PHPUnit\Framework\TestCase;

class SushiBarTest extends TestCase {

    public function providerFreeSeats() {
        return array(
            array(3),
            array(4)
        );
    }

    /**
     * @dataProvider providerFreeSeats
     */    
    function testFreeSeats($val) {

        $Sushibar = new Sushibar("TestBar", $val);

        $result = $Sushibar->freeSeats();
        $this->assertEquals($result, $val);
    }

 
    public function providerFindFreeSeats() {
        return array(
            array(2, array("seatID" => 13, "direction" => "right", "score" => 2)),
            array(1, array("seatID" => 13, "direction" => "right", "score" => 2))
        );
    }

    /**
     * @dataProvider providerFindFreeSeats
     */ 
    function testFindFreeSeats($val, $expectedResult) {

        $Sushibar = new Sushibar("TestBar", 15);
        $Sushibar->incomingGuests(1);
        $Sushibar->incomingGuests(3);
        $Sushibar->incomingGuests(9);
        $Sushibar->groupLeave(1);


        $result = $Sushibar->findFreeSeats($val[0]);

        $this->assertEquals($expectedResult, $result);
    }


    public function providerCreateSushibar() {
        return array(
            array(0),
            array(-100),
            array(null)
        );
    }

    /**
     * @dataProvider providerCreateSushibar
     */ 
    function testCreateSushibar($val) {
        $Sushibar = new Sushibar("",$val);
        $name = $Sushibar->getName();
        $seat_amount = $Sushibar->getAvailalbeSeats();

        $this->assertEquals("F&P Sushibar", $name);
        $this->assertEquals(10, $seat_amount);


    }

}
