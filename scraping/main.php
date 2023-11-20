<?php

// First, run scraper.php
include 'scraper.php';
require_once 'utils/sheetsBlockDateToDb.php';

// Second, find all rows that are blocked and block_date is NULL (new rows found from scraper.php)
require_once 'utils/findNewBlockDates.php';