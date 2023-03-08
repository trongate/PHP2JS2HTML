<?php
class Php2js2html extends Trongate {

  function index() {
    if ((ENV !== 'dev') && (ENV !== 'DEV')) {
      echo 'This module only works when in dev mode!';
      die();
    }

    $this->view('form_page');
  }

  function submit() {
    $function_name = post('function_name', true);
    $new_element_name = post('new_element_name', true);
    $parent_id = post('parent_id', true);
    $num_spaces_per_indent = post('num_spaces_per_indent', true);
    settype($num_spaces_per_indent, 'int');
    $html_code = trim(post('html_code'));

    $js = "function " . $function_name . "() {\n";
    $js .= "const [htmlCode] = document.createDocumentFragment();\n";

    $indent_str = '';
    for ($i = 0;$i < $num_spaces_per_indent;$i++) {
      $indent_str .= '&nbsp;';
    }

    $doc = new DOMDocument();
    $doc->loadHTML($html_code);
    $body = $doc->getElementsByTagName('body')
      ->item(0);
    $nodes = $body->childNodes;
    $tag_counts = array();

    foreach ($nodes as $node) {
      if ($node->nodeType == XML_ELEMENT_NODE) {
        $tag = strtolower($node->tagName);
        $tag_counts[$tag] = isset($tag_counts[$tag]) ? $tag_counts[$tag] + 1 : 1;
        $tag_name = ($tag_counts[$tag] > 1) ? $tag . $tag_counts[$tag] : $tag;

        switch ($tag) {
          case 'select':
            $js = $this->_create_select_js($node, $tag, $tag_name, $js, $doc);
          break;
          case 'ol':
            $js = $this->_create_list_js($node, $tag, $tag_name, $js, $doc);
          break;
          case 'ul':
            $js = $this->_create_list_js($node, $tag, $tag_name, $js, $doc);
          break;
          case 'table':
            $js = $this->_create_table_js($node, $tag, $tag_name, $js, $doc);
          break;
          default:
            $js = $this->_create_node_js($node, $tag, $tag_name, $js, $doc);
          break;
        }
      }
    }

    $js .= "const parentEl = document.getElementById('" . $parent_id . "');\n";
    $js .= "parentEl.appendChild([htmlCode]);";
    $js = str_replace('[htmlCode]', $new_element_name, $js);
    $encoded_js = htmlentities($js);
    $encoded_js = nl2br($encoded_js);
    $encoded_js = str_replace('<br />', '<br />' . $indent_str, $encoded_js);
    $encoded_js .= '<br>}';
    echo "$encoded_js";
  }

  function _create_node_js($node, $tag, $tag_name, $js, $doc) {
    $js .= "const $tag_name = document.createElement('$tag');\n";
    if ($node->hasAttributes()) {
      foreach ($node->attributes as $attr) {
        $name = strtolower($attr->name);
        $value = htmlspecialchars($attr->value);
        $js .= "$tag.setAttribute('$name', '$value');\n";
      }
    }

    if ($node->hasChildNodes()) {
      $children = '';
      foreach ($node->childNodes as $child) {
        $children .= $doc->saveHTML($child);
      }
      $inner_html = $children;
    }

    $inner_html = str_replace(array(
      "\r",
      "\n",
      "\t"
    ) , '', $inner_html);
    $js .= "$tag.innerHTML = `$inner_html`;\n";

    // Add the element to the document body
    $js .= "[htmlCode].appendChild($tag);\n";
    return $js;
  }

  function _create_select_js($node, $tag, $tag_name, $js, $doc) {
    $js .= "const $tag_name = document.createElement('$tag');\n";
    if ($node->hasAttributes()) {
      foreach ($node->attributes as $attr) {
        $name = strtolower($attr->name);
        $value = htmlspecialchars($attr->value);
        $js .= "$tag_name.setAttribute('$name', '$value');\n";
      }
    }

    $options = $node->getElementsByTagName('option');
    foreach ($options as $option) {
      $option_value = htmlspecialchars($option->getAttribute('value'));
      $option_text = htmlspecialchars($option->nodeValue);
      $js .= "const option_$option_value = document.createElement('option');\n";
      $js .= "option_$option_value.value = '$option_value';\n";
      $js .= "option_$option_value.text = '$option_text';\n";
      $js .= "$tag_name.add(option_$option_value);\n";
      if ($option->hasAttribute('selected')) {
        $js .= "option_$option_value.selected = true;\n";
      }
    }

    // Add the element to the document body
    $js .= "[htmlCode].appendChild($tag_name);\n";
    return $js;
  }

  function _create_table_js($node, $tag, $tag_name, $js, $doc) {
    $js .= "const $tag_name = document.createElement('$tag');\n";
    if ($node->hasAttributes()) {
      foreach ($node->attributes as $attr) {
        $name = strtolower($attr->name);
        $value = htmlspecialchars($attr->value);
        $js .= "$tag_name.setAttribute('$name', '$value');\n";
      }
    }

    $rows = $node->getElementsByTagName('tr');
    foreach ($rows as $row) {
      $row_name = "row_" . uniqid();
      $js .= "const $row_name = document.createElement('tr');\n";

      $cols = $row->getElementsByTagName('td');
      foreach ($cols as $col) {
        $col_name = "col_" . uniqid();
        $js .= "const $col_name = document.createElement('td');\n";

        $col_text = htmlspecialchars($col->nodeValue);
        $js .= "$col_name.textContent = '$col_text';\n";

        $rowspan = $col->getAttribute('rowspan');
        if ($rowspan !== "") {
          $js .= "$col_name.rowSpan = '$rowspan';\n";
        }

        $colspan = $col->getAttribute('colspan');
        if ($colspan !== "") {
          $js .= "$col_name.colSpan = '$colspan';\n";
        }

        $align = $col->getAttribute('align');
        if ($align !== "") {
          $js .= "$col_name.align = '$align';\n";
        }

        $valign = $col->getAttribute('valign');
        if ($valign !== "") {
          $js .= "$col_name.vAlign = '$valign';\n";
        }

        $nowrap = $col->getAttribute('nowrap');
        if ($nowrap !== "") {
          $js .= "$col_name.noWrap = true;\n";
        }

        $bgcolor = $col->getAttribute('bgcolor');
        if ($bgcolor !== "") {
          $js .= "$col_name.bgColor = '$bgcolor';\n";
        }

        $background = $col->getAttribute('background');
        if ($background !== "") {
          $js .= "$col_name.background = '$background';\n";
        }

        $style = $col->getAttribute('style');
        if ($style !== "") {
          $js .= "$col_name.setAttribute('style', '$style');\n";
        }

        $js .= "$row_name.appendChild($col_name);\n";
      }

      $js .= "$tag_name.appendChild($row_name);\n";
    }

    // Add the element to the document body
    $js .= "[htmlCode].appendChild($tag_name);\n";
    return $js;
  }

  function _create_list_js($node, $tag, $tag_name, $js, $doc) {
    $js .= "const $tag_name = document.createElement('$tag');\n";
    if ($node->hasAttributes()) {
      foreach ($node->attributes as $attr) {
        $name = strtolower($attr->name);
        $value = htmlspecialchars($attr->value);
        $js .= "$tag_name.setAttribute('$name', '$value');\n";
      }
    }

    $list_items = $node->getElementsByTagName('li');
    foreach ($list_items as $li) {
      $li_value = htmlspecialchars($li->nodeValue);
      $js .= "const li_$li_value = document.createElement('li');\n";
      $js .= "li_$li_value.innerHTML = '$li_value';\n";
      $js .= "$tag_name.appendChild(li_$li_value);\n";

      // Check for nested list items
      $nested_list = $li->getElementsByTagName('ol');
      if ($nested_list->length > 0) {
        $nested_list_js = _create_list_js($nested_list->item(0) , 'ol', "ol_$li_value", '', $doc);
        $js .= "$tag_name.appendChild($nested_list_js);\n";
      }

      $nested_list = $li->getElementsByTagName('ul');
      if ($nested_list->length > 0) {
        $nested_list_js = _create_list_js($nested_list->item(0) , 'ul', "ul_$li_value", '', $doc);
        $js .= "$tag_name.appendChild($nested_list_js);\n";
      }
    }

    // Add the element to the document body
    $js .= "[htmlCode].appendChild($tag_name);\n";
    return $js;
  }

}