<?php
/*
Plugin Name: WGRC WP
Description: This plugin embeds a database view of the WGRC database to a WordPress page using shortcode.
Version: 1.0
*/

// Exit if file is called directly
if (!defined('ABSPATH')) {
  exit;
}

function get_data($table) {
  global $wpdb;
  $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}{$table} LIMIT 100", OBJECT );

  return $results;
}

function remove_duplicate_objects_by_property($data, $property) {
  $properties = array_map(function($obj) {
    return $obj->{$property};
  }, $data);
  
  $unique_properties = array_unique($properties);
  
  return array_values(array_intersect_key($data, $unique_properties));
}

function display_form($data, $table = '') {
  $form = '
  <style>
  form {
    padding: 20px 0;
    display: grid;
    grid-template-columns: repeat(5, 1fr);
  }
  </style>

  <form method="post" action="">
    <label for="table-select">Table:<br>
      <select name="table" id="table-select">
        <option value="">--Please choose a table--</option>
        <option value="genetic_stocks"' . ($table=="genetic_stocks" ? "selected" : "") . '>Genetic Stocks</option>
        <option value="germplasm"' . ($table=="germplasm" ? "selected" : "") . '>Germplasm</option>
      </select>
    </label>

    <label for="type-select">Type:<br>
      <select name="type" id="type-select">
        <option value="">--Please choose a type--</option>
    ';

    $unique_data = remove_duplicate_objects_by_property($data, 'stock_type');
    foreach ($unique_data as $obj) {
      $form .= '<option value="' . $obj->stock_type . '">' . $obj->stock_type . '</option>';
    }
    
    $form .= '
      </select>
    </label>

    <label for="gene-select">Gene:<br>
      <select name="gene" id="gene-select">
        <option value="">--Please choose a gene--</option>
        ';
    
        $unique_data = remove_duplicate_objects_by_property($data, 'genes');

        foreach ($unique_data as $obj) {
          $form .= '<option value="' . $obj->genes . '">' . $obj->genes . '</option>';
        }
        
        $form .= '
      </select>
    </label>

    <label for="chromosome-select">Chromosome:<br>
      <select name="chromosome" id="chromosome-select">
        <option value="">--Please choose a chromosome--</option>
        ';
    
        foreach ($data as $obj) {
          $form .= '<option value="' . $obj->chromosome_of_interest . '">' . $obj->chromosome_of_interest . '</option>';
        }
        
        $form .= '
      </select>
    </label>

    <input type="submit" name="submit" value="Submit">
  </form> 
  <br>
  ';

  return $form;
}

function display_data($data, $table) {
  $results = '
  <style>
  .grid {
    display: grid;
    // border-top: 1px solid black;
    // border-right: 1px solid black;
  }

  .grid > span {
    padding: 8px 4px;
    // border-left: 1px solid black;
    // border-bottom: 1px solid black;    
  }

  .grid > span.gray-header {
    color: #fff;
    font-size: 12px;
    line-height: 1.4;
    text-transform: uppercase;
    background-color: #333333; 
    padding: 20px 24px;
  }

  .grid > span.data-cell {
    font-size: 14px;
    color: #808080;
    line-height: 1.4;
    padding: 14px 24px;
    border-bottom: 1px solid #e5e5e5;
  }
  </style>
  ';

  if ($table == 'genetic_stocks') {
    $results .= '
    <style>
    .grid {
      grid-template-columns: repeat(16, 1fr);
    }
    </style>
    ';

    $results .= '
    <div class="grid">
      <!-- <span class="gray-header">TA_Key</span> -->
      <span class="gray-header">TA Number</span>
      <span class="gray-header">Line Number</span>
      <span class="gray-header">Subline Number</span>
      <span class="gray-header">Type</span>
      <span class="gray-header">Cultivar or Name</span>
      <span class="gray-header">Abbreviation</span>
      <span class="gray-header">Description</span>
      <span class="gray-header">Chromosome of Interest</span>
      <span class="gray-header">Donor Species or Cultivar</span>
      <span class="gray-header">Constitution</span>
      <!-- <span class="gray-header">source</span> -->
      <!-- <span class="gray-header">source_id</span> -->
      <!-- <span class="gray-header">source_seed</span> -->
      <!-- <span class="gray-header">pi_number</span> -->
      <!-- <span class="gray-header">alias</span> -->
      <span class="gray-header">2n=</span>
      <span class="gray-header">Genes</span>
      <span class="gray-header">Chromosome Location</span>
      <span class="gray-header">Phenotypic Trait(s)</span>
      <span class="gray-header">Notes</span>
      <!-- <span class="gray-header">caution</span> -->
      <!-- <span class="gray-header">GBS</span> -->
      <!-- <span class="gray-header">banding</span> -->
      <!-- <span class="gray-header">count</span> -->
      <span class="gray-header">Pedigree</span>
      <!-- <span class="gray-header">last_seed_increase</span> -->
      <!-- <span class="gray-header">other_si</span> -->
      <!-- <span class="gray-header">germ_check</span> -->
      <!-- <span class="gray-header">reference</span> -->
      <!-- <span class="gray-header">acquisition_date</span> -->
      <!-- <span class="gray-header">available</span> -->
    ';

    foreach ($data as $obj) {
      // $results .= "<span class='data-cell'>" . $obj->TA_Key . "</span>";
      $results .= "<span class='data-cell'>" . $obj->TA_number . "</span>";
      $results .= "<span class='data-cell'>" . $obj->line_number . "</span>";
      $results .= "<span class='data-cell'>" . $obj->subline_number . "</span>";
      $results .= "<span class='data-cell'>" . $obj->stock_type . "</span>";
      $results .= "<span class='data-cell'>" . $obj->cultivar_name . "</span>";
      $results .= "<span class='data-cell'>" . $obj->abbreviation . "</span>";
      $results .= "<span class='data-cell'>" . $obj->description . "</span>";
      $results .= "<span class='data-cell'>" . $obj->chromosome_of_interest . "</span>";
      $results .= "<span class='data-cell'>" . $obj->donor_species_cultivar . "</span>";
      $results .= "<span class='data-cell'>" . $obj->constitution . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->source . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->source_id . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->source_seed . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->pi_number . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->alias . "</span>";
      $results .= "<span class='data-cell'>" . $obj->two_n_equals . "</span>";
      $results .= "<span class='data-cell'>" . $obj->genes . "</span>";
      $results .= "<span class='data-cell'>" . $obj->chromosome_location . "</span>";
      $results .= "<span class='data-cell'>" . $obj->phenotypic_traits . "</span>";
      $results .= "<span class='data-cell'>" . $obj->notes . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->caution . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->GBS . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->banding . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->count . "</span>";
      $results .= "<span class='data-cell'>" . $obj->pedigree . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->last_seed_increase . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->other_si . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->germ_check . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->reference . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->acquisition_date . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->available . "</span>";
    }
  } elseif ($table == 'germplasm') {
    $results .= '
    <style>
    .grid {
      grid-template-columns: repeat(25, 1fr);
    }
    </style>
    ';

    $results .= '
    <div class="grid">
      <!-- <span class="gray-header">TA Key</span> -->
      <span class="gray-header">TA Number</span>
      <span class="gray-header">Line Number</span>
      <!-- <span class="gray-header">PUID</span> -->
      <!-- <span class="gray-header">INSTCODE</span> -->
      <span class="gray-header">ACCESS NUMBER</span>
      <span class="gray-header">COLLECTION NUMBER</span>
      <span class="gray-header">COLLECTION CODE</span>
      <span class="gray-header">GENUS</span>
      <span class="gray-header">SPECIES</span>
      <span class="gray-header">SPECIES AUTHOR</span>
      <span class="gray-header">SUBTAXA</span>
      <span class="gray-header">VARIETY</span>
      <span class="gray-header">SUBTAXA AUTHOR</span>
      <span class="gray-header">ACCESS NAME</span>
      <!-- <span class="gray-header">ACQUISITION DATE</span> -->
      <span class="gray-header">ORIGINAL COUNTRY</span>
      <span class="gray-header">COLLECTION SITE</span>
      <span class="gray-header">DECLINATION LATITUDE</span>
      <span class="gray-header">DECLINATION LONGITUDE</span>
      <span class="gray-header">ELEVATION</span>
      <!-- <span class="gray-header">GEOREFMETH</span> -->
      <span class="gray-header">COLLECTION DATE</span>
      <!-- <span class="gray-header">SAMPSTAT</span> -->
      <!-- <span class="gray-header">COLLECTION SOURCE</span> -->
      <span class="gray-header">DONOR CODE</span>
      <span class="gray-header">DONOR NAME</span>
      <span class="gray-header">DONOR NUMBER</span>
      <span class="gray-header">OTHER NUMBER</span>
      <!-- <span class="gray-header">STORAGE</span> -->
      <span class="gray-header">REMARKS</span>
      <!-- <span class="gray-header">LAST_SEED_INCREASE</span> -->
      <!-- <span class="gray-header">OTHER_SI</span> -->
      <span class="gray-header">CORE</span>
      <span class="gray-header">Available</span>
    ';

    foreach ($data as $obj) {
      // $results .= "<span class='data-cell'>" . $obj->TA_Key . "</span>";
      $results .= "<span class='data-cell'>" . $obj->TA_number . "</span>";
      $results .= "<span class='data-cell'>" . $obj->line_number . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->PUID . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->INSTCODE . "</span>";
      $results .= "<span class='data-cell'>" . $obj->ACCENUMB . "</span>";
      $results .= "<span class='data-cell'>" . $obj->COLLNUMB . "</span>";
      $results .= "<span class='data-cell'>" . $obj->COLLCODE . "</span>";
      $results .= "<span class='data-cell'>" . $obj->GENUS . "</span>";
      $results .= "<span class='data-cell'>" . $obj->SPECIES . "</span>";
      $results .= "<span class='data-cell'>" . $obj->SPAUTHOR . "</span>";
      $results .= "<span class='data-cell'>" . $obj->SUBTAXA . "</span>";
      $results .= "<span class='data-cell'>" . $obj->VARIETY . "</span>";
      $results .= "<span class='data-cell'>" . $obj->SUBTAUTHOR . "</span>";
      $results .= "<span class='data-cell'>" . $obj->ACCENAME . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->ACQDATE . "</span>";
      $results .= "<span class='data-cell'>" . $obj->ORIGCTY . "</span>";
      $results .= "<span class='data-cell'>" . $obj->COLLSITE . "</span>";
      $results .= "<span class='data-cell'>" . $obj->DECLATITUDE . "</span>";
      $results .= "<span class='data-cell'>" . $obj->DECLONGITUDE . "</span>";
      $results .= "<span class='data-cell'>" . $obj->ELEVATION . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->GEOREFMETH . "</span>";
      $results .= "<span class='data-cell'>" . $obj->COLLDATE . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->SAMPSTAT . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->COLLSRC . "</span>";
      $results .= "<span class='data-cell'>" . $obj->DONORCODE . "</span>";
      $results .= "<span class='data-cell'>" . $obj->DONORNAME . "</span>";
      $results .= "<span class='data-cell'>" . $obj->DONORNUMB . "</span>";
      $results .= "<span class='data-cell'>" . $obj->OTHERNUMB . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->STORAGE . "</span>";
      $results .= "<span class='data-cell'>" . $obj->REMARKS . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->LAST_SEED_INCREASE . "</span>";
      // $results .= "<span class='data-cell'>" . $obj->OTHER_SI . "</span>";
      $results .= "<span class='data-cell'>" . $obj->CORE . "</span>";
      $results .= "<span class='data-cell'>" . $obj->Available . "</span>";
    }
  }
  
  $results .= "</div>";

  return $results;
}

function handle_shortcode() {
  $display = '';

  if (isset($_POST['submit'])) {
    $table = $_POST['table'];
    // if ($table == 'genetic_stocks') {
    //   $display .= "Table: <b>Genetic Stocks</b><br><br>";
    // } elseif ($table = 'germplasm') {
    //   $display .= "Table: <b>Germplasm</b><br><br>";
    // }

    $data = get_data($table);
    $display .= display_form($data, $table);

    $display .= display_data($data, $table);
  } else {
    $display .= display_form(NULL, '');
  }

  return $display;
}
add_shortcode('latestPosts', 'handle_shortcode');