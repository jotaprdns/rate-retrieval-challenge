Rate Retrieval Challenge – Pure PHP

This project implements the technical challenge using pure PHP, without any framework. The goal is to authenticate against the ShipPrimus API using JWT, handle login, refresh, and token expiration, retrieve rate data for a specific vendor, and process the response to extract the cheapest rate per service level.

⸻

Technologies Used
	•	PHP 8.x
	•	Native cURL
	•	Composer (for PSR-4 autoloading, without external dependencies)

⸻

Project Structure

rate-retrieval-challenge/
    public/
        index.php
    src/
        Http/
            ApiClient.php
        Auth/
            TokenManager.php
        Service/
            RateService.php
        Helpers/
            RateHelper.php
    config/
        config.php
    storage/
        token.json
    composer.json
    README.md

Installation
	1.	Clone or extract the project.
	2.	Navigate to the project directory.
	3.	Run: composer dump-autoload

This generates the required autoload files.

⸻

How to Run

From the root directory: php public/index.php

The script performs the following steps:
	1.	Logs in to the API if no token exists.
	2.	If the token is expired, attempts to refresh it.
	3.	If refresh fails, performs a new login.
	4.	Calls the rates endpoint.
	5.	Prints all returned rates.
	6.	Calculates and prints the cheapest rate per service level.

⸻

Token Handling

The TokenManager.php file contains all logic related to JWT handling:
	•	Initial login.
	•	Reading data.accessToken from the real API response.
	•	Validating expiration by decoding the JWT’s exp field.
	•	Attempting refresh before falling back to login.
	•	Storing the token locally in storage/token.json.

⸻

Rates Endpoint Consumption

RateService.php is responsible for calling: GET /database/vendor/contract/{vendorId}/rate

and converting the response into a structured array with:
	•	CARRIER
	•	SERVICE LEVEL
	•	RATE TYPE
	•	TOTAL
	•	TRANSIT TIME

⸻

Helper: Cheapest Rate Per Service Level

RateHelper.php contains the function: getCheapestRatesByServiceLevel(array $rates)

This function groups rates by service level and returns only the option with the lowest TOTAL value within each category.

⸻

Notes
	•	The project is implemented without any framework, but follows a clear modular structure.
	•	New endpoints can be added easily by reusing ApiClient and TokenManager.
	•	The execution process is simple and documented for straightforward review.

⸻


