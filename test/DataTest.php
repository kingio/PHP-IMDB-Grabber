<?php
class DataTest extends PHPUnit_Framework_TestCase
{
    protected $requiredFields = [
        'type' => ':string',
        'released' => ':boolean',
        'year' => ':int',
        'title' => ':string',
        'release_date' => ':string',
    ];

    protected $optionalFields = [
        'seasons' => ':int',
        'description' => ':string',
        'age_rating' => ':int',
        'runtime' => ':int',
        'genre' => [
            ':string'
        ],
        'country' => [
            ':string'
        ],
        'director' => [
            '*' => [
                'imdb' => ':imdb',
                'name' => ':string',
            ]
        ],
        'cast' => [
            '*' => [
                'name' => ':string',
                'imdb' => ':imdb',
                'role' => ':string',
            ]
        ],
        'company' => [
            '*' => [
                'imdb' => ':imdb',
                'name' => ':string',
            ]
        ],
        'writer' => [
            '*' => [
                'imdb' => ':imdb',
                'name' => ':string',
            ]
        ],
        'akas' => [
            '*' => [
                'title' => ':string',
                'country' => ':string',
            ]
        ],
        'languages' => [
            ':string'
        ],
        'releases' => [
            ':string' => ':date'
        ]
    ];
    

    protected $validators = [];

    /**
     * DataTest constructor.
     * @param null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        
        $allFields = array_merge($this->requiredFields, $this->optionalFields);

        \Mascame\Katina\Rules::setRules([
           'imdb' => function($value) {
               preg_match("/([0-9]+){5,9}/", $value, $matches);

               return (sizeof($matches) > 0);
           }
        ]);

        $this->validators['basic'] = new \Mascame\Katina\Validator($this->requiredFields, $this->optionalFields);
        $this->validators['full-serie'] = new \Mascame\Katina\Validator($allFields, []);

        unset($allFields['seasons']);

        $this->validators['full-movie'] = new \Mascame\Katina\Validator($allFields, []);
    }


    /**
     * @dataProvider imdbProvider
     *
     * @param $imdbId
     * @param \Mascame\Katina\Validator $validator
     * @throws Exception
     */
    public function testMediaExtraction($imdbId, \Mascame\Katina\Validator $validator)
    {
        //create the url
        $imdb_url = 'http://www.imdb.com/title/tt' . $imdbId . '/';

        //get essential information
        $IMDB = new \IMDB\IMDB($imdb_url);

        if ($IMDB->isReady) {
            $data = $this->getAll($IMDB);

            // Remove empty optionals
            foreach ($data as $key => $values) {
                if (empty($values) && array_key_exists($key, $this->optionalFields)) {
                    unset($data[$key]);
                }
            }

            $integral = false;

            try {
                $integral = $validator->check($data);
            } catch (\Exception $e) {}

            $this->assertTrue($integral);

        } else {
            throw new Exception("Error Processing Request", 1);
        }

    }

    /**
     * @param $IMDB \IMDB\IMDB
     * @return array
     */
    protected function getAll($IMDB) {
        return [
            'type' => $IMDB->getType(),
            'seasons' => $IMDB->getSeasons(),
            'genre' => $IMDB->getGenre(),
            'age_rating' => $IMDB->getAgeRating(),
            'runtime' => $IMDB->getRuntime(),
            'year' => $IMDB->getYear(),
            'title' => $IMDB->getTitle(),
            'country' => $IMDB->getCountry(),
            'director' => $IMDB->getDirector(),
            'writer' => $IMDB->getWriter(),
            'company' => $IMDB->getCompany(),
            'description' => $IMDB->getDescription(),
            'akas' => $IMDB->getAkas(),
            'cast' => $IMDB->getCastAndCharacter(),
            'languages' => $IMDB->getLanguages(),
            'released' => $IMDB->isReleased(),
            'releases' => $IMDB->getReleases(),
            'release_date' => $IMDB->getReleaseDate(),
            'episodes' => $IMDB->getEpisodes()
        ];
    }

    public function imdbProvider()
    {
        
        return [
            // Fantaghiro5
            ["tt0140039", $this->validators['basic']],

            // Rubicon
            ["tt1389371", $this->validators['basic']],

            // Punch
            ["tt4329922", $this->validators['basic']],

            // Johnny Ringo
            ["tt0060589", $this->validators['basic']],

            // Interstellar
            ["tt0816692", $this->validators['full-movie']],

            // Isla Mínima
            ["tt3253930", $this->validators['full-movie']],

            // The Missing
            ["tt3877200", $this->validators['full-serie']],

            // BreakingBad
            ["tt0903747", $this->validators['full-serie']],
        ];
    }
}
