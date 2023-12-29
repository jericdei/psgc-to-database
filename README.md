# PSGC to Database

This is a CLI application using Artisan console that aims to convert the latest Philippine Standard Geographic Code (PSGC) Data to Database (MySQL/PostgreSQL).

The original PSGC file is a Microsoft Excel File and this app will read and write on separate tables.

## Requirements

-   PHP 8 and above
-   MySQL or PostgreSQL
-   No need for a web server since it's just a CLI application.

## Usage

### Clone this repository and setup Laravel

```
git clone git@github.com:jericdei/psgc-to-database.git

cd psgc-to-database

composer install
```

### Download the latest PSGC Excel File

```
php artisan psgc:dl-latest
```

This will go to the [PSGC Website](https://psa.gov.ph/classification/psgc) and will try to download the latest data as an Excel spreadsheet. It uses [Symfony DOM Crawler](https://github.com/symfony/dom-crawler) to achieve this.

The file will be stored at `/storage/app/public/psgc/latest.xlsx`

### Read the Excel file and convert to Database

```
php artisan psgc:convert
```

This will read the Excel file using [PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet) and loop over the rows, then save it on a database table based on the location type.

#### Location Types / Tables

-   Region
-   Province
-   Municipality
-   Sub-municipality
-   City
-   Barangay
