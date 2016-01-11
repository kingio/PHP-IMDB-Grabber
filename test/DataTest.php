<?php
class DataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider imdbProvider
     */
    public function testMediaExtraction($imdbId, $expected)
    {
        //create the url
        $imdb_url = 'http://www.imdb.com/title/tt' . $imdbId . '/';

        //get essentian information
        $IMDB = new \IMDB\IMDB($imdb_url);

        if ($IMDB->isReady) {
            $this->assertEquals($expected['type'], $IMDB->getType(), "Check Type");
            $this->assertEquals($expected['released'], $IMDB->isReleased(), "Check IsReleased");
            $this->assertEquals($expected['seasons'], $IMDB->getSeasons(), "Check Seasons");
            $this->assertEquals($expected['genre'], $IMDB->getGenre(), "Check Genre");
            $this->assertEquals($expected['runtime'], $IMDB->getRuntime(), "Check Runtime");
            $this->assertEquals($expected['year'], $IMDB->getYear(), "Check Year");
            $this->assertEquals($expected['title'], $IMDB->getTitle(), "Check Title");
            $this->assertEquals($expected['country'], $IMDB->getCountry(), "Check Country");
            $this->assertEquals($expected['release_date'], $IMDB->getReleaseDate(), "Check ReleaseDate");
            $this->assertEquals($expected['director'], $IMDB->getDirector(), "Check Director");
            $this->assertEquals($expected['writer'], $IMDB->getWriter(), "Check Writer");
            $this->assertEquals($expected['company'], $IMDB->getCompany(), "Check Company");
            $this->assertEquals($expected['description'], $IMDB->getDescription(), "Check Description");
            //only test one

            if(is_array($expected['akas']) && sizeof($expected['akas']) > 0){
                $this->assertEquals($expected['akas'][0], $IMDB->getAkas()[0], "Check Akas");
            } else {
                $this->assertEquals($expected['akas'], $IMDB->getAkas(), "Check Akas as empty array");
            }

            if(is_array($expected['cast']) && sizeof($expected['cast']) > 0){
                $this->assertEquals($expected['cast'][0], $IMDB->getCastAndCharacter()[0], "Check Cast");
            } else {
                $this->assertEquals($expected['cast'], $IMDB->getCastAndCharacter(), "Check Cast as empty");
            }

            $this->assertEquals($expected['languages'], $IMDB->getLanguages(), "Check Languages");

        } else {
            throw new Exception("Error Processing Request", 1);
        }

    }

    public function imdbProvider()
    {
        $expectedRubicon = require_once 'data/rubicon.php';
        $expectedPunch = require_once 'data/punch.php';
        $expectedInterstellar = require_once 'data/interstellar.php';
        $expectedIslaMinima = require_once 'data/isla-minima.php';
        $expectedJohnnyRingo = require_once 'data/johnny-ringo.php';
        $expectedFantaghiro5 = require_once 'data/fantaghiro5.php';
        $expectedTheMissing = require_once 'data/the-missing.php';
        $expectedBreakingBad = require_once 'data/breaking-bad.php';

        return array(
            array("tt1389371", $expectedRubicon),
            array("tt4329922", $expectedPunch),
            array("tt0816692", $expectedInterstellar),
            array("tt3253930", $expectedIslaMinima),
            array("tt0060589", $expectedJohnnyRingo),
            array("tt0140039", $expectedFantaghiro5),
            array("tt3877200", $expectedTheMissing),
            array("tt0903747", $expectedBreakingBad),
        );
    }
}
?>