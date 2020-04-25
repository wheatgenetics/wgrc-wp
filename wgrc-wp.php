<?php
/*
Plugin Name: WGRC WP
Description: This plugin embeds a database view of the WGRC database to a WordPress page using shortcode.
The WGRC data has already been imported into the WordPress database with the creation of two tables: wp_genetic_stocks and wp_germplasm
Version: 1.0
*/

// Exit if file is called directly
if (!defined('ABSPATH')) {
  exit;
}

class WgrcData {
  function __construct() {
    add_action('init', array($this, 'register_scripts'));
    add_action('wp_enqueue_scripts', array($this, 'wgrc_scripts'));
    add_shortcode('wgrc-db', array($this, 'handle_shortcode'));
  }

  function register_scripts() {
    wp_register_style('wgrc-style', plugins_url('/css/style.css', __FILE__), false, '1.0.0', 'all');
    wp_register_script( 'wgrc-form', plugins_url('/js/form.js', __FILE__), array('jquery'), '2.5.1' );
  }
  
  function wgrc_scripts() {
    wp_enqueue_style('wgrc-style');
    wp_enqueue_script('wgrc-form');
  }

  function get_select_options($table, $select_box_name) {
    global $wpdb;

    $results = $wpdb->get_results("SELECT DISTINCT {$select_box_name} FROM {$wpdb->prefix}{$table} WHERE {$select_box_name} <> ''", OBJECT);
    return $results;
  }

  function get_total_pages($table, $where_clause) {
    global $wpdb;

    $total_rows = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}{$table} {$where_clause}");
    $total_pages = ceil($total_rows / 30);
    
    return $total_pages;
  }
  
  function get_data($table, $where_clause, $offset) {
    global $wpdb;

    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$table} {$where_clause} LIMIT $offset, 30", OBJECT);

    return $results;
  }

  function display_form($data, $selected_table = '', $selected_stock_type = '', $selected_gene = '', $selected_chromosome_of_interest = '', $selected_genus = '', $selected_species = '', $selected_subtaxa = '') {
    $form = '
    <form id="wgrc-wp-form" method="get" action="">
      <label for="table-select"><strong>Table:</strong><br>
        <select name="table" id="table-select">
          <option value="">--Please choose a table--</option>
          <option value="genetic_stocks"' . ($selected_table=="genetic_stocks" ? "selected" : "") . '>Genetic Stocks</option>
          <option value="germplasm"' . ($selected_table=="germplasm" ? "selected" : "") . '>Germplasm</option>
        </select>
      </label>
      ';

      if ($data) {
        if ($selected_table == 'genetic_stocks') {
          $form .= '
          <label for="type-select"><strong>Type:</strong><br>
            <select name="type" id="type-select">
              <option value="">--Please choose a type--</option>
          ';

          $stock_types = $this->get_select_options($selected_table, 'stock_type');
          
          foreach ($stock_types as $stock_type) {
            $form .= '<option value="' . $stock_type->stock_type . '"' . ($stock_type->stock_type==$selected_stock_type ? "selected" : "") . '>' . $stock_type->stock_type . '</option>';
          }
          
          $form .= '
            </select>
          </label>

          <label for="gene-select"><strong>Gene:</strong><br>
            <select name="gene" id="gene-select">
              <option value="">--Please choose a gene--</option>
              ';
          
              $genes = $this->get_select_options($selected_table, 'genes');
              
              foreach ($genes as $gene) {
                $form .= '<option value="' . $gene->genes . '"' . ($gene->genes==$selected_gene ? "selected" : "") . '>' . $gene->genes . '</option>';
              }
              
              $form .= '
            </select>
          </label>

          <label for="chromosome-select"><strong>Chromosome:</strong><br>
            <select name="chromosome" id="chromosome-select">
              <option value="">--Please choose a chromosome--</option>
              ';
          
              $chromosomes_of_interest = $this->get_select_options($selected_table, 'chromosome_of_interest');
              
              foreach ($chromosomes_of_interest as $chromosome_of_interest) {
                $form .= '<option value="' . $chromosome_of_interest->chromosome_of_interest . '"' . ($chromosome_of_interest->chromosome_of_interest==$selected_chromosome_of_interest ? "selected" : "") . '>' . $chromosome_of_interest->chromosome_of_interest . '</option>';
              }
              
              $form .= '
            </select>
          </label>
          ';
        } elseif ($selected_table == 'germplasm') {
          $form .= '
          <label for="genus-select"><strong>Genus:</strong><br>
            <select name="genus" id="genus-select">
              <option value="">--Please choose a genus--</option>
          ';

          $genera = $this->get_select_options($selected_table, 'GENUS');

          foreach ($genera as $genus) {
            $form .= '<option value="' . $genus->GENUS . '"' . ($genus->GENUS==$selected_genus ? "selected" : "") . '>' . $genus->GENUS . '</option>';
          }
          
          $form .= '
            </select>
          </label>

          <label for="species-select"><strong>Species:</strong><br>
            <select name="species" id="species-select">
              <option value="">--Please choose a species--</option>
              ';
          
              $all_species = $this->get_select_options($selected_table, 'SPECIES');
              
              foreach ($all_species as $species) {
                $form .= '<option value="' . $species->SPECIES . '"' . ($species->SPECIES==$selected_species ? "selected" : "") . '>' . $species->SPECIES . '</option>';
              }
              
              $form .= '
            </select>
          </label>

          <label for="subtaxa-select"><strong>Subtaxa:</strong><br>
            <select name="subtaxa" id="subtaxa-select">
              <option value="">--Please choose a subtaxa--</option>
              ';
          
              $all_subtaxa = $this->get_select_options($selected_table, 'SUBTAXA');
              
              foreach ($all_subtaxa as $subtaxa) {
                $form .= '<option value="' . $subtaxa->SUBTAXA . '"' . ($subtaxa->SUBTAXA==$selected_subtaxa ? "selected" : "") . '>' . $subtaxa->SUBTAXA . '</option>';
              }
              
              $form .= '
            </select>
          </label>

          <p>View these entries at <a href="https://www.genesys-pgr.org/a/overview/v275pxlWdY4" target="_blank">Genesys</a></p>
          ';
        }
      }

      $form .= '
    </form>
    <br>
    ';

    return $form;
  }

  function display_pagination($pageno, $total_pages) {
    $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    
    // if ($pageno < $total_pages) {
      $current_url = preg_replace('/&pageno=\d+/', '', $current_url);
    // }
    
    $pagination = '
    <div>
      <a href="' . $current_url . '&pageno=1">First</a>
      <a href="' . $current_url;

      if ($pageno <= 1) {
        $pagination .= '&pageno=' . ($pageno);
      } else {
        $pagination .= "&pageno=" . ($pageno - 1);
      } 
      $pagination .= '">Prev</a>

      <a href="' . $current_url;
      
      if ($pageno >= $total_pages) {
        $pagination .= '&pageno=' . ($pageno);
      } else {
        $pagination .= '&pageno=' . ($pageno + 1);
      }
      $pagination .= '">Next</a>

      <a href="' . $current_url . '&pageno=' . $total_pages . '">Last</a>
    </div>';

    return $pagination;
  }

  function display_data($data, $table) {
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
        <span class="gray-header">2n=</span>
        <span class="gray-header">Genes</span>
        <span class="gray-header">Chromosome Location</span>
        <span class="gray-header">Phenotypic Trait(s)</span>
        <span class="gray-header">Notes</span>
        <span class="gray-header">Pedigree</span>
      ';

      foreach ($data as $obj) {
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
        $results .= "<span class='data-cell'>" . $obj->two_n_equals . "</span>";
        $results .= "<span class='data-cell'>" . $obj->genes . "</span>";
        $results .= "<span class='data-cell'>" . $obj->chromosome_location . "</span>";
        $results .= "<span class='data-cell'>" . $obj->phenotypic_traits . "</span>";
        $results .= "<span class='data-cell'>" . $obj->notes . "</span>";
        $results .= "<span class='data-cell'>" . $obj->pedigree . "</span>";
      }
    } elseif ($table == 'germplasm') {
      $results .= '
      <style>
      .grid {
        grid-template-columns: repeat(21, 1fr);
      }
      </style>
      ';

      $results .= '
      <div class="grid">
        <span class="gray-header">TA Number</span>
        <span class="gray-header">Line Number</span>
        <span class="gray-header">COLLECTION NUMBER</span>
        <span class="gray-header">COLLECTION CODE</span>
        <span class="gray-header">GENUS</span>
        <span class="gray-header">SPECIES</span>
        <span class="gray-header">SUBTAXA</span>
        <span class="gray-header">VARIETY</span>
        <span class="gray-header">ORIGINAL COUNTRY</span>
        <span class="gray-header">COLLECTION SITE</span>
        <span class="gray-header">DECLINATION LATITUDE</span>
        <span class="gray-header">DECLINATION LONGITUDE</span>
        <span class="gray-header">ELEVATION</span>
        <span class="gray-header">COLLECTION DATE</span>
        <span class="gray-header">DONOR CODE</span>
        <span class="gray-header">DONOR NAME</span>
        <span class="gray-header">DONOR NUMBER</span>
        <span class="gray-header">OTHER NUMBER</span>
        <span class="gray-header">REMARKS</span>
        <span class="gray-header">CORE</span>
        <span class="gray-header">Available</span>
      ';

      foreach ($data as $obj) {
        $results .= "<span class='data-cell'>" . $obj->TA_number . "</span>";
        $results .= "<span class='data-cell'>" . $obj->line_number . "</span>";
        $results .= "<span class='data-cell'>" . $obj->COLLNUMB . "</span>";
        $results .= "<span class='data-cell'>" . $obj->COLLCODE . "</span>";
        $results .= "<span class='data-cell'>" . $obj->GENUS . "</span>";
        $results .= "<span class='data-cell'>" . $obj->SPECIES . "</span>";
        $results .= "<span class='data-cell'>" . $obj->SUBTAXA . "</span>";
        $results .= "<span class='data-cell'>" . $obj->VARIETY . "</span>";
        $results .= "<span class='data-cell'>" . $obj->ORIGCTY . "</span>";
        $results .= "<span class='data-cell'>" . $obj->COLLSITE . "</span>";
        $results .= "<span class='data-cell'>" . $obj->DECLATITUDE . "</span>";
        $results .= "<span class='data-cell'>" . $obj->DECLONGITUDE . "</span>";
        $results .= "<span class='data-cell'>" . $obj->ELEVATION . "</span>";
        $results .= "<span class='data-cell'>" . $obj->COLLDATE . "</span>";
        $results .= "<span class='data-cell'>" . $obj->DONORCODE . "</span>";
        $results .= "<span class='data-cell'>" . $obj->DONORNAME . "</span>";
        $results .= "<span class='data-cell'>" . $obj->DONORNUMB . "</span>";
        $results .= "<span class='data-cell'>" . $obj->OTHERNUMB . "</span>";
        $results .= "<span class='data-cell'>" . $obj->REMARKS . "</span>";
        $results .= "<span class='data-cell'>" . $obj->CORE . "</span>";
        $results .= "<span class='data-cell'>" . $obj->Available . "</span>";
      }
    }
    
    $results .= "</div>";

    return $results;
  }

  function handle_shortcode() {
    $display = '';

    if (isset($_GET['table'])) {
      $table = $_GET['table'];
    }

    // genetic_stocks parameters
    $stock_type = $_GET['stock_type'];
    $genes = $_GET['genes'];
    $chromosome_of_interest = $_GET['chromosome_of_interest'];

    // germplasm parameters
    $genus = $_GET['genus'];
    $species = $_GET['species'];
    $subtaxa = $_GET['subtaxa'];

    // Pagination
    if (isset($_GET['pageno'])) {
      $pageno = $_GET['pageno'];
    } else {
      $pageno = 1;
    }
    $offset = ($pageno-1) * 30;

    $where_clause = ''; // No WHERE clause has been created yet.

    if ($stock_type) {
      $where_clause = 'WHERE stock_type = "' . $stock_type . '"';
    } elseif ($genes) {
      $where_clause = 'WHERE genes = "' . $genes . '"';
    } elseif ($chromosome_of_interest) {
      $where_clause = 'WHERE chromosome_of_interest = "' . $chromosome_of_interest . '"';
    } elseif ($genus) {
      $where_clause = 'WHERE GENUS = "' . $genus . '"';
    } elseif ($species) {
      $where_clause = 'WHERE SPECIES = "' . $species . '"';
    } elseif ($subtaxa) {
      $where_clause = 'WHERE SUBTAXA = "' . $subtaxa . '"';
    }

    $total_pages = $this->get_total_pages($table, $where_clause);

    $data = $this->get_data($table, $where_clause, $offset);

    $display .= $this->display_form($data, $table, $stock_type, $genes, $chromosome_of_interest, $genus, $species, $subtaxa);

    $display .= $this->display_pagination($pageno, $total_pages);

    $display .= $this->display_data($data, $table);

    return $display;
  }
}

new WgrcData();