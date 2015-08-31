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

        //interestellar
        $expectedInterstellar = array();
        $expectedInterstellar['type'] = "movie";
        $expectedInterstellar['released'] = true;
        $expectedInterstellar['seasons'] = 0;
        $expectedInterstellar['genre'] = array('Adventure','Drama','Sci-Fi');
        $expectedInterstellar['runtime'] = 169;
        $expectedInterstellar['year'] = 2014;
        $expectedInterstellar['title'] = "Interstellar";
        $expectedInterstellar['country'] = array('USA','UK','Canada');
        $expectedInterstellar['release_date'] = "7 November 2014  (USA)";
        $expectedInterstellar['director'] = array(array('imdb' =>"0634240","name" =>"Christopher Nolan"));
        $expectedInterstellar['writer'] = array(array('imdb' =>"0634300","name" =>"Jonathan Nolan"),array('imdb' =>"0634240","name" =>"Christopher Nolan"));
        $expectedInterstellar['company'] = array( array(
            'imdb'=>'0023400',
            'name'=>'Paramount Pictures'
        ),
            array(
                'imdb'=>'0026840',
                'name'=>'Warner Bros.'
            ),
            array(
                'imdb'=>'0159111',
                'name'=>'Legendary Pictures'
            ));
        $expectedInterstellar['languages'] = array("English");
        $expectedInterstellar['cast'] = array( array(
            "name" => "Ellen Burstyn",
            "imdb" => "0000995",
            "role" => "Murph"
        ),
            array(

                "name" => "Matthew McConaughey",
                "imdb" => "0000190",
                "role" => "Cooper"
            )
        );

        $expectedInterstellar['akas'] = array( array(
            "title" => "Interestelar",
            "country" => "Argentina"
        ),array(
            "title" => "Ulduzlararasi",
            "country" => "Azerbaijan"
        )
        );


        $expectedInterstellar['description'] = "A team of explorers travel through a wormhole in space in an attempt to ensure humanity's survival.";


        //punch (n/A in Tviso)
        $expectedPunch = array();
        $expectedPunch['type'] = "TV Series";
        $expectedPunch['released'] = true;
        $expectedPunch['seasons'] = 1;
        $expectedPunch['genre'] = array('Drama','Romance', 'Thriller');
        $expectedPunch['runtime'] = 0;
        $expectedPunch['year'] = 2014;
        $expectedPunch['title'] = "Punch";
        $expectedPunch['country'] = array('South Korea');
        $expectedPunch['release_date'] = "15 December 2014  (South Korea)";
        $expectedPunch['director'] = array(
            0 =>
                array (
                    'imdb' => '6118272',
                    'name' => 'Myung Woo Lee',
                ),
        );
        $expectedPunch['writer'] = array(
            0 =>
                array (
                    'imdb' => '5781092',
                    'name' => 'Kyung-soo Park',
                ),
        );
        $expectedPunch['company'] = array( array(
            'imdb'=>'0215344',
            'name'=>'HB Entertainment'
        ));
        $expectedPunch['languages'] = array("Korean");
        $expectedPunch['cast'] = array( array(
            "name" => "Rae-won Kim",
            "imdb" => "0453640",
            "role" => "Park Jung-Hwan"
        ),
            array(

                "name" => "Ah-jung Kim",
                "imdb" => "2098258",
                "role" => "Shin Ha-Gyung"
            )
        );

        $expectedPunch['akas'] = array();


        $expectedPunch['description'] = "n/A";


        //Rubicon (n/A in Tviso's calendar)
        $expectedRubicon = array();
        $expectedRubicon['type'] = "TV Series";
        $expectedRubicon['released'] = true;
        $expectedRubicon['seasons'] = 1;
        $expectedRubicon['genre'] = array('Crime','Drama', 'Mystery', 'Thriller');
        $expectedRubicon['runtime'] = 45;
        $expectedRubicon['year'] = 2010;
        $expectedRubicon['title'] = "Rubicon";
        $expectedRubicon['country'] = array('USA');
        $expectedRubicon['release_date'] = "1 August 2010  (USA)";
        $expectedRubicon['director'] = array(
            0 =>
                array (
                    'imdb' => '0687964',
                    'name' => 'Jeremy Podeswa',
                ),
            1 =>
                array (
                    'imdb' => '0026442',
                    'name' => 'Brad Anderson',
                ),
            2 =>
                array (
                    'imdb' => '0080601',
                    'name' => 'Ed Bianchi',
                ),
            3 =>
                array (
                    'imdb' => '0111303',
                    'name' => 'Henry Bromell',
                ),
            4 =>
                array (
                    'imdb' => '0002339',
                    'name' => 'Allen Coulter',
                ),
            5 =>
                array (
                    'imdb' => '0272704',
                    'name' => 'Guy Ferland',
                ),
            6 =>
                array (
                    'imdb' => '0327064',
                    'name' => 'Nick Gomez',
                ),
            7 =>
                array (
                    'imdb' => '0330360',
                    'name' => 'Keith Gordon',
                ),
            8 =>
                array (
                    'imdb' => '0542960',
                    'name' => 'Seith Mann',
                ),
            9 =>
                array (
                    'imdb' => '0002399',
                    'name' => 'Alik Sakharov',
                ),
            10 =>
                array (
                    'imdb' => '0806252',
                    'name' => 'Michael Slovis',
                ),
            11 =>
                array (
                    'imdb' => '0851930',
                    'name' => 'Alan Taylor',
                ),
        );
        $expectedRubicon['writer'] = array(
            0 =>
                array (
                    'imdb' => '0395842',
                    'name' => 'Jason Horwitch',
                ),
            1 =>
                array (
                    'imdb' => '0730446',
                    'name' => 'Richard Robbins',
                ),
            2 =>
                array (
                    'imdb' => '2469074',
                    'name' => 'Nichole Beattie',
                ),
            3 =>
                array (
                    'imdb' => '0111303',
                    'name' => 'Henry Bromell',
                ),
            4 =>
                array (
                    'imdb' => '0163910',
                    'name' => 'Eliza Clark',
                ),
            5 =>
                array (
                    'imdb' => '0923738',
                    'name' => 'Zack Whedon',
                ),
            6 =>
                array (
                    'imdb' => '1398578',
                    'name' => 'Blake Masters',
                ),
            7 =>
                array (
                    'imdb' => '1339659',
                    'name' => 'Michael Oates Palmer',
                ),
        );
        $expectedRubicon['company'] = array( array(
            'imdb'=>'0183230',
            'name'=>'Warner Horizon Television'
        ),
            array(
                'imdb'=>'0019701',
                'name'=>'American Movie Classics (AMC)'
            ));
        $expectedRubicon['languages'] = array("English");
        $expectedRubicon['cast'] = array( array(
            "name" => "James Badge Dale",
            "imdb" => "0197647",
            "role" => "Will Travers"
        ),
            array(
                "name" => "Jessica Collins",
                "imdb" => "2193754",
                "role" => "Maggie Young"
            )
        );

        $expectedRubicon['akas'] = array(
            array(
                "title" => "Rubicón",
                "country" => "Spain"
            ),array(
                "title" => "Рубикон",
                "country" => "Russia"
            )
        );


        $expectedRubicon['description'] = "Will Travers is an analyst at a New York City-based federal intelligence agency who is thrown into a story where nothing is as it appears to be.";



        //Isla minima
        $expectedIslaMinima = array();
        $expectedIslaMinima['type'] = "movie";
        $expectedIslaMinima['released'] = true;
        $expectedIslaMinima['seasons'] = 0;
        $expectedIslaMinima['genre'] = array('Crime','Thriller');
        $expectedIslaMinima['runtime'] = 105;
        $expectedIslaMinima['year'] = 2014;
        $expectedIslaMinima['title'] = "La isla mínima";
        $expectedIslaMinima['country'] = array('Spain');
        $expectedIslaMinima['release_date'] = "26 September 2014  (Spain)";
        $expectedIslaMinima['director'] = array(array("imdb" => '0735705',"name" => 'Alberto Rodríguez'));
        $expectedIslaMinima['writer'] = array( array(
            'imdb'=>'1943791',
            'name'=>'Rafael Cobos'
        ),
            array(
                'imdb'=>'0735705',
                'name'=>'Alberto Rodríguez'
            ));

        $expectedIslaMinima['company'] = array(
            array(
                'imdb'=>'0225092',
                'name'=>'AXN'
            ),
            array(
                'imdb'=>'0435170',
                'name'=>'Atresmedia Cine'
            ),
            array(
                'imdb'=>'0445833',
                'name'=>'Atresmedia'
            ),
        );

        $expectedIslaMinima['languages'] = array("Spanish");
        $expectedIslaMinima['cast'] = array( array(
            "name" => "Javier Gutiérrez",
            "imdb" => "0349522",
            "role" => "Juan"
        ),
            array(
                "name" => "Raúl Arévalo",
                "imdb" => "1666855",
                "role" => "Pedro"
            )
        );



        $expectedIslaMinima['akas'] =  array(
            0 =>
                array (
                    'title' => 'La isla mínima',
                    'country' => 'Chile',
                ),
            1 =>
                array (
                    'title' => 'Marshland',
                    'country' => 'Denmark',
                ),
            2 =>
                array (
                    'title' => 'Το μικρό νησί',
                    'country' => 'Greece',
                ),
            3 =>
                array (
                    'title' => 'Mocsárvidék',
                    'country' => 'Hungary',
                ),
            4 =>
                array (
                    'title' => 'Stare grzechy maja dlugie cienie',
                    'country' => 'Poland',
                ),
            5 =>
                array (
                    'title' => 'Najmanje ostrvo',
                    'country' => 'Serbia',
                ),
            6 =>
                array (
                    'title' => 'Миниатюрный остров',
                    'country' => 'Russia',
                ),
            7 =>
                array (
                    'title' => 'Marshland',
                    'country' => 'Sweden',
                ),
        );

        $expectedIslaMinima['description'] = "In the MARSHLAND a serial killer is on the loose. Two homicide detectives who appear to be poles apart must settle their differences and bring the murderer to justice before more young women lose their lives.";


        $expectedJohnnyRingo = array (
            'type' => 'movie',
            'released' => true,
            'seasons' => 0,
            'genre' =>
                array (
                    0 => 'Western',
                ),
            'runtime' => 0,
            'year' => 1966,
            'title' => 'Uccidete Johnny Ringo',
            'country' =>
                array (
                    0 => 'Italy',
                ),
            'release_date' => '6 May 1966  (Italy)',
            'director' =>
                array (
                    0 =>
                        array (
                            'imdb' => '0049653',
                            'name' => 'Gianfranco Baldanello',
                        ),
                ),
            'writer' =>
                array (
                    0 =>
                        array (
                            'imdb' => '0220383',
                            'name' => 'Arpad DeRiso',
                        ),
                    1 =>
                        array (
                            'imdb' => '0220383',
                            'name' => 'Arpad DeRiso',
                        ),
                ),
            'company' =>
                array (
                    0 =>
                        array (
                            'imdb' => '0127454',
                            'name' => 'La Cine Associati',
                        ),
                ),
            'description' => 'n/A',
            'akas' =>
                array (
                    0 =>
                        array (
                            'title' => 'Kill Johnny Ringo',
                            'country' => '',
                        ),
                    1 =>
                        array (
                            'title' => 'Johnny Ringo, O Caçador dos Fora da Lei',
                            'country' => 'Brazil',
                        ),
                    2 =>
                        array (
                            'title' => 'Dræb Johnny Ringo',
                            'country' => 'Denmark',
                        ),
                    3 =>
                        array (
                            'title' => 'Tuez Johnny Ringo',
                            'country' => 'France',
                        ),
                    4 =>
                        array (
                            'title' => 'Jag dödade Johnny Ringo',
                            'country' => 'Sweden',
                        ),
                ),
            'cast' =>
                array (
                    0 =>
                        array (
                            'name' => 'Brett Halsey',
                            'imdb' => '0357020',
                            'role' => 'Johnny Ringo',
                        ),
                    1 =>
                        array (
                            'name' => 'Greta Polyn',
                            'imdb' => '0689960',
                            'role' => 'Annie',
                        ),
                    2 =>
                        array (
                            'name' => 'Guido Lollobrigida',
                            'imdb' => '0518179',
                            'role' => 'Sheriff Parker /              Lee Mellin',
                        ),
                    3 =>
                        array (
                            'name' => 'Nino Fuscagni',
                            'imdb' => '0299270',
                            'role' => 'Ray Scott',
                        ),
                    4 =>
                        array (
                            'name' => 'Angelo Dessy',
                            'imdb' => '0221778',
                            'role' => 'Jackson',
                        ),
                    5 =>
                        array (
                            'name' => 'Barbara Loy',
                            'imdb' => '0523420',
                            'role' => 'Christine Scott',
                        ),
                    6 =>
                        array (
                            'name' => 'Guglielmo Spoletini',
                            'imdb' => '0819336',
                            'role' => 'José',
                        ),
                    7 =>
                        array (
                            'name' => 'Franco Gulà',
                            'imdb' => '0347904',
                            'role' => 'Whiskey Pete',
                        ),
                    8 =>
                        array (
                            'name' => 'Ugo Carboni',
                            'imdb' => '0136227',
                            'role' => '-',
                        ),
                    9 =>
                        array (
                            'name' => 'Franco Castellani',
                            'imdb' => '0144669',
                            'role' => '-',
                        ),
                    10 =>
                        array (
                            'name' => 'Federico Chentrens',
                            'imdb' => '0155697',
                            'role' => '-',
                        ),
                    11 =>
                        array (
                            'name' => 'Agostino De Simone',
                            'imdb' => '0211613',
                            'role' => '-',
                        ),
                    12 =>
                        array (
                            'name' => 'Consalvo Dell\'Arti',
                            'imdb' => '0037876',
                            'role' => '-',
                        ),
                    13 =>
                        array (
                            'name' => 'Attilio Dottesio',
                            'imdb' => '0234581',
                            'role' => '-',
                        ),
                    14 =>
                        array (
                            'name' => 'Guy Galley',
                            'imdb' => '0302822',
                            'role' => '-',
                        ),
                ),
            'languages' =>
                array (
                    0 => 'Italian',
                ),
        );

        return array(
            array("tt1389371", $expectedRubicon),
            array("tt4329922", $expectedPunch),
            array("tt0816692", $expectedInterstellar),
            array("tt3253930", $expectedIslaMinima),
            array("tt0060589", $expectedJohnnyRingo),
        );
    }
}
?>