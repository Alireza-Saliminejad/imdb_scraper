<?php

/**
 * How it works?
 * 
 * This PHP code is for searching movie data from the IMDb API and then store that data into the MySQL database. It starts by defining a class named 'MovieSearch'.
 * The constructor function of this class takes an API key as an argument for accessing the IMDb API.
 * This class has two methods, 'searchMovieByTitle' and 'getMovieDataById', both of these take a movie title and ID (respectively) as arguments to search for movie data.
 * 'searchMovieByTitle' sends a request to the IMDb API and gets search results in JSON format. If the data is present, then it passes the movie ID to 'getMovieDataById'
 * method.
 * The 'getMovieDataById' method requests IMDb API to get data of a particular movie using its ID. The response is also in JSON format. The method then extracts relevant
 * data and formats it in an array to store in the database via the 'insertIntoDatabase' method.
 * The 'insertIntoDatabase' method inserts movie data into the MySQL database that is defined inside the method itself. It also formats the movie data so that it would
 * show nicely in the HTML template blocks. Next, it creates a movie info block using HTML and displays this on the screen.
 *
 * Lastly, this code creates an instance of the 'MovieSearch' class and uses the searchMovieByTitle method to populate the database with movie data. The required movie
 * title is fetched from the user and passed to this method.
 * 
 * @author Alireza Saliminejad
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL
 */

 
/**
 * Class MovieSearch
 */
class MovieSearch 
{
    /**
     * @var string The API key for accessing the IMDb API
     */
    private $apiKey;

    /**
     * MovieSearch constructor.
     *
     * @param string $apiKey The API key for accessing the IMDb API
     */
    public function __construct(string $apiKey) 
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Search for a movie by title.
     *
     * @param string $title The title of the movie to search for
     *
     * @return void
     */
    public function searchMovieByTitle(string $title): void 
    {
        $url      = "https://imdb-api.com/en/API/SearchMovie/" . $this->apiKey . "/" . rawurlencode($title);
        $response = file_get_contents($url);
        $result   = json_decode($response);

        if ($result->errorMessage) 
        {
            echo $result->errorMessage;
        } 
        else 
        {
            $id = $result->results[0]->id;
            $this->getMovieDataById($id);
        }
    }

    /**
     * Get movie data by ID.
     *
     * @param string $id The ID of the movie to get data for
     *
     * @return void
     */
    private function getMovieDataById(string $id): void 
    {
        $url      = "https://imdb-api.com/en/API/Title/" . $this->apiKey . "/" . $id;
        $response = file_get_contents($url);
        $data     = json_decode($response);

        //@formatter:off
        $movie_data = array(
            "title"        => $data->title              ,
            "year"         => $data->year               ,
            "plot"         => $data->plot               ,
            "type"         => $data->type               ,
            "release_date" => $data->releaseDate        ,
            "award"        => $data->awards             ,
            "director"     => $data->directors          ,
            "writer"       => $data->writers            ,
            "stars"        => $data->stars              ,
            "company"      => $data->companies          ,
            "language"     => $data->languages          ,
            "rating"       => $data->imDbRating         ,
            "rating_votes" => $data->imDbRatingVotes    ,
            "metacritic"   => $data->metacriticRating   ,
            "keyword"      => $data->keywords
        );
        //@formatter:on

        $this->insertIntoDatabase($movie_data);
    }

    /**
     * Insert movie data into the database.
     *
     * @param array $movie_data The movie data to insert into the database
     *
     * @return void
     */
    private function insertIntoDatabase(array $movie_data): void 
    {
        $db_servername = "localhost";
        $db_username   = "root";
        $db_name       = "mediadb";

        $conn = new mysqli($db_servername, $db_username, null, $db_name);
        if ($conn->connect_error) 
        {
            die("Connection failed: " . $conn->connect_error);
        }

        //@formatter:off
        $title        = $conn->real_escape_string($movie_data['title'           ]);
        $year         = $conn->real_escape_string($movie_data['year'            ]);
        $plot         = $conn->real_escape_string($movie_data['plot'            ]);
        $type         = $conn->real_escape_string($movie_data['type'            ]);
        $release_date = $conn->real_escape_string($movie_data['release_date'    ]);
        $awards       = $conn->real_escape_string($movie_data['award'           ]);
        $directors    = $conn->real_escape_string($movie_data['director'        ]);
        $writers      = $conn->real_escape_string($movie_data['writer'          ]);
        $stars        = $conn->real_escape_string($movie_data['stars'           ]);
        $companies    = $conn->real_escape_string($movie_data['company'         ]);
        $languages    = $conn->real_escape_string($movie_data['language'        ]);
        $rating       = $conn->real_escape_string($movie_data['rating'          ]);
        $rating_votes = $conn->real_escape_string($movie_data['rating_votes'    ]);
        $metacritic   = $conn->real_escape_string($movie_data['metacritic'      ]);
        $keywords     = $conn->real_escape_string($movie_data['keyword'         ]);
        //@formatter:on

        $sql = "INSERT INTO movie (title, year, plot, type, release_date, award, writer, stars, company, language, director, rating, rating_votes, metacritic, keyword)
        VALUES ('$title', '$year', '$plot', '$type', '$release_date', '$awards','$writers', '$stars', '$companies', '$languages', '$directors', '$rating', '$rating_votes', '$metacritic', '$keywords')";

        if ($conn->query($sql)) 
        {
            $style = "
    <style>
        div.movie-info {
            border: 2px solid black;
            padding: 10px;
            margin-bottom: 20px;
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
        }
        
        div.movie-info h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        div.movie-info p {
            margin-bottom: 5px;
        }
        
        div.movie-info strong {
            font-weight: bold;
        }
    </style>
";

            // HTML code for the movie info block
            //@formatter:off
            $movie_info = "
    <div class='movie-info'>
        <h2>{$movie_data['title']}</h2>
        <p><strong>Year:            </strong> {$movie_data['year'           ]}         </p>
        <p><strong>Plot:            </strong> {$movie_data['plot'           ]}         </p>
        <p><strong>Type:            </strong> {$movie_data['type'           ]}         </p>
        <p><strong>Release Date:    </strong> {$movie_data['release_date'   ]}         </p>
        <p><strong>Award:           </strong> {$movie_data['award'          ]}         </p>
        <p><strong>Director:        </strong> {$movie_data['director'       ]}         </p>
        <p><strong>Writer:          </strong> {$movie_data['writer'         ]}         </p>
        <p><strong>Stars:           </strong> {$movie_data['stars'          ]}         </p>
        <p><strong>Company:         </strong> {$movie_data['company'        ]}         </p>
        <p><strong>Language:        </strong> {$movie_data['language'       ]}         </p>
        <p><strong>Rating:          </strong> {$movie_data['rating'         ]}         </p>
        <p><strong>Rating Votes:    </strong> {$movie_data['rating_votes'   ]}         </p>
        <p><strong>Metacritic:      </strong> {$movie_data['metacritic'     ]}         </p>
        <p><strong>Keyword:         </strong> {$movie_data['keyword'        ]}         </p>
    </div>
";
            //@formatter:on

            // Output the movie info block
            echo $style . $movie_info;
        }
        else
        {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }

        $conn->close();
    }
}

$apiKey = "k_n3lp9s66";
if (isset($_POST['movieName']))
{
    $movie_title = $_POST['movieName'];
}
$movieSearch = new MovieSearch($apiKey);
$movieSearch->searchMovieByTitle($movie_title);
ssddd