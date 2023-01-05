<?php

/*
htmlSQL - version 0.6
--------------------------------------------------------------------
htmlSQL is a experimental library to query websites or HTML code with
an SQL-like language.

AUTHOR: Jonas John (http://www.jonasjohn.de/)

The latest version of htmlSQL can be obtained from:
https://github.com/hxseven/htmlSQL

LICENSE:
--------------------------------------------------------------------
Copyright (c) 2006 Jonas John. All rights reserved.
--------------------------------------------------------------------
Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:

- Redistributions of source code must retain the above copyright
  notice, this list of conditions and the following disclaimer.
- Redistributions in binary form must reproduce the above copyright
  notice, this list of conditions and the following disclaimer in
  the documentation and/or other materials provided with the distribution.
- Neither the name of Jonas John nor the names of its contributors
  may be used to endorse or promote products derived from this
  software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

--------------------------------------------------------------------

CHANGELOG:

0.5 -> 0.6 (January 04, 2023)
- Remove each() function. Use foreach.
- php4 -> php5/7/8
- Mini-refactoring

0.4 -> 0.5 (May 07, 2006):
- Renamed the project from webSQL to htmlSQL because webSQL already exists
- Added more error checks
- Added the convert_tagname_to_key function and fixed a few issues

0.1 -> 0.4 (April 2006):
- Created main parts of the library

*/

class htmlsql
{
    private $is_test = false;

    // configuration:
    public $version = '0.6';
    public $referer = '';
    public $user_agent = 'htmlSQL/0.6';


    public $snoopy = NULL; // holds snoopy object
    public $error = '';

    // the downloaded page is stored in here:
    private $page = '';

    private $results = array();
    private $results_objects = NULL;


    public function setTestMode()
    {
        $this->is_test = true;
    }

    /*
    ** init_snoopy
    **
    ** initializes the snoopy class
    */
    private function init_snoopy()
    {
        $this->snoopy = new Snoopy();
        $this->snoopy->agent = $this->user_agent;
        $this->snoopy->referer = $this->referer;
        $this->snoopy->curl_path = '/usr/bin/curl'; // Без этого, разумеется, не работает. И это, по-хорошему, нужно получать из конфига.
    }

    /*
    ** set_user_agent
    **
    ** set a custom user agent
    */
    public function set_user_agent($u)
    {
        $this->user_agent = $u;
    }

    /*
    ** set_referer
    **
    ** sets the referer
    */
    public function set_referer($r)
    {
        $this->referer = $r;
    }

    /*
    ** get_between
    **
    ** returns the content between $start and $end
    */
    private function get_between($content, $start, $end)
    {
        $r = explode($start, $content);
        if (isset($r[1]))
        {
            $r = explode($end, $r[1]);
            return $r[0];
        }

        return '';
    }

    /*
    ** isolate_content
    **
    ** isolates the content to a specific part
    */
    public function isolate_content($start, $end)
    {
        $this->page = $this->get_between($this->page, $start, $end);
    }

    /*
    ** connect
    **
    ** connects to a data source (url, file or string)
    */
    public function connect($type, $resource)
    {

        if ($type == 'url')
        {
            return $this->fetch_url($resource);
        }
        else if ($type == 'file')
        {

            if (!file_exists($resource))
            {
                $this->error = 'The given file "'.$resource.' does not exist!';
                return false;
            }

            $this->page = file_get_contents($resource);
            return true;
        }
        else if ($type == 'string')
        {
            $this->page = $resource;
            return true;
        }

        return false;
    }

    /*
    ** fetch_url
    **
    ** downloads the given URL with snoopy
    */
    private function fetch_url($url)
    {

        $parsed_url = parse_url($url);

        if (isset($parsed_url['scheme']) === false)
        {
            $this->error = 'Scheme not found!';
            return false;
        }
        $scheme = $parsed_url['scheme'];
        if (($scheme != 'http') and ($scheme != 'https'))
        {
            $this->error = 'Unsupported URL sheme given, please just use "HTTP" or "HTTPS".';
            return false;
        }
        if (!isset($parsed_url['host']) or $parsed_url['host'] == '')
        {
            $this->error = 'Invalid URL given!';
            return false;
        }

        $host = $parsed_url['host'];
        $host .= (isset($parsed_url['port']) and  !empty($parsed_url['port'])) ? ':'.$parsed_url['port'] : '';
        $path = (isset($parsed_url['path']) and  !empty($parsed_url['path'])) ? $parsed_url['path'] : '/';
        $path .= (isset($parsed_url['query']) and  !empty($parsed_url['query'])) ? '?'.$parsed_url['query'] : '';

        $url = $scheme . '://' . $host . $path;

        $this->init_snoopy();

        if ($this->snoopy->fetch($url))
        {
            $this->page = $this->snoopy->results;
            $this->snoopy->results = '';
        }
        else
        {
            $this->error = 'Could not establish a connection to the given URL! :-(' . PHP_EOL . $this->snoopy->error . PHP_EOL;
            return false;
        }

        return true;
    }

    /*
    ** select
    **
    ** restricts the content of a specific tag
    */
    public function select($tagname, $num = 0)
    {
        $num++;
        if ($tagname != '')
        {

            preg_match('/<'.$tagname.'.*?>(.*?)<\/'.$tagname.'>/is', $this->page, $m);

            if (isset($m[$num]) and !empty($m[$num]))
            {
                $this->page = $m[$num];
            }
            else
            {
                $this->error = 'Could not select tag: "'.$tagname.'('.$num.')"!';
                return false;
            }
        }
        return true;
    }

    /*
    ** get_content
    **
    ** returns the content of an request
    */
    public function get_content()
    {
        return $this->page;
    }

    /*
    ** query
    **
    ** performs a query
    */
    public function query($term)
    {
        // query results are stored in here:
        $this->results = NULL;
        $this->results_objects = NULL;

        $term = trim($term);
        if ($term == '')
        {
            $this->error = 'Empty query given!';
            return false;
        }

        // match query:
        preg_match('/^SELECT (.*?) FROM (.*)$/i', $term, $m);

        // parse returns values
        // SELECT * FROM ...
        // SELECT foo,bar FROM ...
        $return_values = isset($m[1]) ? trim($m[1]) : '*';
        if ($return_values != '*')
        {
            $return_values = explode(',', strtolower($return_values));
            $return_values = $this->clean_array($return_values);
        }

        // match from and where part:
        //
        // ... FROM * WHERE $id=="one"
        // ... FROM a WHERE $class=="red"
        // ... FROM a
        // ... FROM *
        $last = isset($m[2]) ? trim($m[2]) : '';

        $search_term = '';
        $where_term = '';

        if (preg_match('/^(.*?) WHERE (.*?)$/i', $last, $m))
        {
            $search_term = trim($m[1]);
            $where_term = trim($m[2]);
        }
        else
        {
            $search_term = $last;
        }

        // find tags
        $tag_attributes = array();
        $tag_values = array();
        if ($search_term == '*')
        {
            // search all
            $tag_names = array();
            $html = $this->page;

            $this->extract_all_tags($html, $tag_names, $tag_attributes, $tag_values);
            $results = $this->match_tags($return_values, $where_term, $tag_attributes, $tag_values, $tag_names);
        }
        else
        {
            // search term is a tag

            $tagname = trim($search_term);

            // $regexp = '<'.$tagname.'([ \t].*?|)>((.*?)<\/'.$tagname.'>)?';
            // $regexp = '<'.$tagname.'(\s.*?|)>((.*?)?)'; // это регулярное выражение верно находит все классы и другие атрибуты, но не находит текст :-(
            $regexp = '<'.$tagname.'(\s.*?|)>((.*?)<\/'.$tagname.'>)?';
            preg_match_all('/'.$regexp.'/is', $this->page, $m);

            if (count($m[0]) != 0)
            {
                $tag_attributes = $m[1];
                $tag_values = $m[3];
            }

            $results = $this->match_tags($return_values, $where_term, $tag_attributes, $tag_values, $tagname);
        }

        $this->results = $results;

        // was there a error during the search process?
        return ($this->error == '');
    }

    /*
    ** clean_array
    **
    **
    */
    private function clean_array($arr)
    {
        $new = array();
        for ($i = 0; $i < count($arr); $i++)
        {
            $arr[$i] = trim($arr[$i]);
            if ($arr[$i] != '')
            {
                $new[] = $arr[$i];
            }
        }
        return $new;
    }

    /*
    ** extract_all_tags
    **
    **
    */
    private function extract_all_tags($html, &$tag_names, &$tag_attributes, &$tag_values, $depth = 0)
    {
        // stop endless loops -> ugly...
        if ($depth > 99999)
            return;

        preg_match_all('/<([a-z0-9\-]+)(.*?)>((.*?)<\/\1>)?/is', $html, $m);

        if (count($m[0]) === 0)
            return;

        for ($tag_index = 0; $tag_index < count($m[0]); $tag_index++)
        {
            $tag_names[] = trim($m[1][$tag_index]);
            $tag_attributes[] = trim($m[2][$tag_index]);
            $tag_values[] = trim($m[4][$tag_index]);

            // go deeper:
            if (trim($m[4][$tag_index]) != '' and preg_match('/<[a-z0-9\-]+.*?>/is', $m[4][$tag_index]))
            {
                $this->extract_all_tags($m[4][$tag_index], $tag_names, $tag_attributes, $tag_values, $depth+1);
            }
        }
    }

    /*
    ** match_tags
    **
    **
    */
    private function match_tags($return_values, $where_term, $tag_attributes, $tag_values, $tag_names)
    {
        $results = array();
        $search_mode = '';
        $search_attribute = '';
        $search_term = '';

        /*
        ** parse:
        **
        ** href LIKE ".htm"
        ** class = "foo"
        */

        $search_mode = ($where_term == '') ? 'match_all' : 'eval';

        for ($i = 0; $i < count($tag_attributes); $i++)
        {
            $tag_attributes[$i] = $this->parse_attributes($tag_attributes[$i]);

            if (is_array($tag_names))
            {
                $tag_attributes[$i]['tagname'] = isset($tag_names[$i]) ? $tag_names[$i] : '';
            }
            else
            {
                $tag_attributes[$i]['tagname'] = $tag_names; // string
            }

            $tag_attributes[$i]['text'] = isset($tag_values[$i]) ? $tag_values[$i] : '';

            if ($search_mode == 'eval')
            {
                if ($this->test_tag($tag_attributes[$i], $where_term))
                {
                    $results[] = $this->add_result($return_values, $tag_attributes[$i]);
                }
            }
            else if ($search_mode == 'match_all')
            {
                $results[] = $this->add_result($return_values, $tag_attributes[$i]);
            }
        }

        return $results;
    }

    /*
    ** parse_attributes
    **
    ** parses HTML attributes and returns an array
    */
    private function parse_attributes($attrib)
    {
        // strtolower не будет работать с двухбайтными кодировками!!!

        $attrib .= '>';
        $mode = 'search_key';
        $tmp = '';
        $current_key = '';
        $attributes = array();

        for ($x=0; $x < strlen($attrib); $x++)
        {
            $char = $attrib[$x];

            if ($char == '=' and $mode == 'search_key')
            {
                $current_key = trim($tmp);
                $tmp = '';
                $mode = 'value';
            }
            else if ($mode == 'search_key' and preg_match('/[ \t\s\r\n>]/', $char))
            {
                $current_key = strtolower(trim($tmp));
                if ($current_key != '')
                    $attributes[$current_key] = '';
                $tmp = ''; $current_key = '';
            }
            else if ($mode == 'value' and $char == '"'){ $mode = 'find_value_ending_a'; }
            else if ($mode == 'value' and $char == '\''){ $mode = 'find_value_ending_b'; }
            else if ($mode == 'value'){ $tmp .= $char; $mode = 'find_value_ending_c'; }
            else if (
                ($mode == 'find_value_ending_a' and $char == '"') or
                ($mode == 'find_value_ending_b' and $char == '\'') or
                ($mode == 'find_value_ending_c' and preg_match('/[ \t\s\r\n>]/', $char))
            ){

                $mode = 'search_key';

                if ($current_key != '')
                {
                    $current_key = strtolower($current_key);
                    $attributes[$current_key] = $tmp;
                }
                $tmp = '';
            }
            else
                $tmp .= $char;
        }

        if ($mode != 'search_key' and $current_key != '')
        {
            $current_key = strtolower($current_key);
            $attributes[$current_key] = trim(preg_replace('/>+$/', '', $tmp));
        }

        return $attributes;
    }

    /*
    ** add_result
    **
    **
    */
    private function add_result($return_values, $tag_attributes)
    {
        if ($return_values === '*')
            return $tag_attributes;

        $new_result = array();
        for ($tag_index = 0; $tag_index < count($return_values); $tag_index++)
        {
            $tagname = explode(' as ', $return_values[$tag_index]);
            $caption = $return_values[$tag_index];

            if (count($tagname) !== 1)
            {
                $caption = trim($tagname[1]);
                $tagname = trim($tagname[0]);
            }
            else
                $tagname = $caption;

            $new_result[$caption] = isset($tag_attributes[$tagname]) ? $tag_attributes[$tagname] : '';
        }

        return $new_result;
    }

    /*
    ** test_tag
    **
    **
    */
    private function test_tag($tag_attributes, $if_term)
    {
        preg_match_all('/\$([a-z0-9_\-]+)/i', $if_term, $m);
        if (isset($m[1]))
        {
            for ($i = 0; $i < count($m[1]); $i++)
            {
                $varname = $m[1][$i];
                $$varname = '';
            }
        }

        $new_list = array();
        foreach ($tag_attributes as $key => $value)
        {
            $key = preg_replace('/[^a-z0-9_\-]/i', '', $key);
            if ($key != '')
            {
                $new_list[$key] = $value;
            }
        }

        unset($tag_attributes);
        extract($new_list);

        $r = false;
        if (@eval('$r = ('.$if_term.');') === false)
        {
            $this->error = 'The WHERE statement is invalid (eval() failed)!';
            return false;
        }

        return $r; // получается, можно просто заменить на return true
    }

    /*
    ** convert_tagname_to_key
    **
    ** converts the tagname to the array key
    */
    public function convert_tagname_to_key()
    {
        $new_array = array();
        $tag_name = '';

        foreach ($this->results as $key => $val)
        {
            if (isset($val['tagname']))
            {
                $tag_name = $val['tagname'];
                unset($val['tagname']);
            }
            else
            {
                $tag_name = '(empty)';
            }

            $new_array[$tag_name] = $val;
        }

        $this->results = $new_array;
    }

    /*
    ** fetch_array
    **
    ** returns the results as an array
    */
    public function fetch_array()
    {
        return $this->results;
    }

    /*
    ** fetch_objects
    **
    ** returns the results as objects
    */
    public function fetch_objects()
    {
        if ($this->results_objects == NULL)
        {
            $results = array();

            foreach ($this->results as $key => $val)
            {
                $results[$key] = $this->array2object($val);
            }

            $this->results_objects = $results;
        }

        return $this->results_objects;
    }

    /*
    ** array2object
    **
    ** converts an array to an object
    */
    private function array2object($array)
    {
        if (is_array($array))
        {
            $obj = new StdClass();
            foreach ($array as $key => $value)
            {
                $obj->$key = $value;
            }
        }
        else
            $obj = $array;

        return $obj;
    }

    /*
    ** get_result_count
    **
    ** returns the number of results
    */
    public function get_result_count()
    {
        return count($this->results);
    }

}
