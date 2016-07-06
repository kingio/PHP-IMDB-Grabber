<?php
class DataTest extends PHPUnit_Framework_TestCase
{
    protected $requiredFields = [
        'type' => 'string',
        'released' => 'boolean',
        'year' => 'int',
        'title' => 'string',
        'release_date' => 'string',
    ];

    protected $optionalFields = [
        'genre' => 'array',
        'seasons' => 'int',
        'country' => 'array',
        'director' => 'array',
        'cast' => 'array',
        'description' => 'string',
        'company' => 'array',
        'writer' => 'array',
        'runtime' => 'string',
        'akas' => 'array',
        'languages' => 'array',
        'releases' => 'array'
    ];

    protected $arrayFields = [
        'genre' => [
            "type" => "singleValue",
            "value" => "string"
        ],
        'country' => [
            "type" => "singleValue",
            "value" => "string"
        ],
        'director' => [
            "type" => "singleValue",
            "value" => "string"
        ],
        'cast' => [
            "type" => "singleValue",
            "value" => "string"
        ],
        'company' => [
            "type" => "singleValue",
            "value" => "string"
        ],
        'writer' => [
            "type" => "singleValue",
            "value" => "string"
        ],
        'akas' => [
            "type" => "singleValue",
            "value" => "string"
        ],
        'languages' => [
            "type" => "singleValue",
            "value" => "string"
        ],
        'releases' => [
            "type" => "singleValue",
            "value" => "string"
        ],
    ];

    protected $validators = [];

    /**
     * DataTest constructor.
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        require_once "OutputValidator.php";

        $allFields = array_merge($this->requiredFields, $this->optionalFields);

        $this->validators['basic'] = new OutputValidator($this->requiredFields, $this->optionalFields, $this->arrayFields);
        $this->validators['full-serie'] = new OutputValidator($allFields, [], $this->arrayFields);

        unset($allFields['seasons']);

        $this->validators['full-movie'] = new OutputValidator($allFields, [], $this->arrayFields);
    }


    /**
     * @dataProvider imdbProvider
     */
    public function testMediaExtraction($imdbId, OutputValidator $validator)
    {
        //create the url
        $imdb_url = 'http://www.imdb.com/title/tt' . $imdbId . '/';

        //get essential information
        $IMDB = new \IMDB\IMDB($imdb_url);

        if ($IMDB->isReady) {
            $data = $this->getAll($IMDB);
            $integral = false;

            try {
                $integral = $validator->checkIntegrity($data);
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
