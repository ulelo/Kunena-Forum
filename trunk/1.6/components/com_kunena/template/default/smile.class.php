<?php
/**
* @version $Id$
* Kunena Component
* @package Kunena
*
* @Copyright (C) 2008 - 2010 Kunena Team All rights reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link http://www.kunena.com
*
* Based on FireBoard Component
* @Copyright (C) 2006 - 2007 Best Of Joomla All rights reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link http://www.bestofjoomla.com
*
* Based on Joomlaboard Component
* @copyright (C) 2000 - 2004 TSMF / Jan de Graaff / All Rights Reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author TSMF & Jan de Graaff
**/
defined( '_JEXEC' ) or die();

include_once(KUNENA_PATH_LIB .DS. "kunena.parser.base.php");
include_once(KUNENA_PATH_LIB .DS. "kunena.parser.php");

class smile
{
    function smileParserCallback($fb_message, $history, $emoticons, $iconList = null)
    {
        // from context HTML into HTML

        // where $history can be 1 or 0. If 1 then we need to load the grey
        // emoticons for the Topic History. If 0 we need the normal ones

	static $regexp_trans = array('/' => '\/', '^' => '\^', '$' => '\$', '.' => '\.', '[' => '\[', ']' => '\]', '|' => '\|', '(' => '\(', ')' => '\)', '?' => '\?', '*' => '\*', '+' => '\+', '{' => '\{', '}' => '\}', '\\' => '\\\\', '^' => '\^', '-' => '\-');

		$utf8 = (KUNENA_CHARSET == 'UTF-8') ? "u" : "";
        $type = ($history == 1) ? "-grey" : "";
        $message_emoticons = array();
        $message_emoticons = $iconList? $iconList : smile::getEmoticons($history);
        // now the text is parsed, next are the emoticons
	    $fb_message_txt = $fb_message;

        if ($emoticons != 1)
        {
            reset($message_emoticons);

            foreach ($message_emoticons as $emo_txt => $emo_src)
            {
				$emo_txt = strtr($emo_txt, $regexp_trans);
				// Check that smileys are not part of text like:soon (:s)
                $fb_message_txt = preg_replace('/(\W|\A)'.$emo_txt.'(\W|\Z)/'.$utf8, '\1<img src="' . $emo_src . '" alt="" style="vertical-align: middle;border:0px;" />\2', $fb_message_txt);
				// Previous check causes :) :) not to work, workaround is to run the same regexp twice
                $fb_message_txt = preg_replace('/(\W|\A)'.$emo_txt.'(\W|\Z)/'.$utf8, '\1<img src="' . $emo_src . '" alt="" style="vertical-align: middle;border:0px;" />\2', $fb_message_txt);
            }
        }

        return $fb_message_txt;
    }

    function smileReplace($fb_message, $history, $emoticons, $iconList = null)
    {

        $fb_message_txt = $fb_message;

        //implement the new parser
        $parser = new TagParser();
        $interpreter = new KunenaBBCodeInterpreter($parser);
        $task = $interpreter->NewTask();
        $task->SetText($fb_message_txt.' _EOP_');
        $task->dry = FALSE;
        $task->drop_errtag = FALSE;
	    $task->history = $history;
	    $task->emoticons = $emoticons;
	    $task->iconList = $iconList;
        $task->Parse();

        return JString::substr($task->text,0,-6);
    }
    /**
    * function to retrieve the emoticons out of the database
    *
    * @author Niels Vandekeybus <progster@wina.be>
    * @version 1.0
    * @since 2005-04-19
    * @param boolean $grayscale
    *            determines wether to return the grayscale or the ordinary emoticon
    * @param boolean  $emoticonbar
    *            only list emoticons to be displayed in the emoticonbar (currently unused)
    * @return array
    *             array consisting of emoticon codes and their respective location (NOT the entire img tag)
    */
    function getEmoticons($grayscale, $emoticonbar = 0)
    {
        $kunena_db = &JFactory::getDBO();
        $grayscale == 1 ? $column = "greylocation" : $column = "location";
        $sql = "SELECT code, `$column` FROM #__fb_smileys";

        if ($emoticonbar == 1)
        $sql .= " WHERE emoticonbar='1'";

        $kunena_db->setQuery($sql);
        $smilies = $kunena_db->loadObjectList();
        	check_dberror("Unable to load smilies.");

        $smileyArray = array();
        foreach ($smilies as $smiley) {                                                    // We load all smileys in array, so we can sort them
            $smileyArray[$smiley->code] = '' . KUNENA_URLEMOTIONSPATH . $smiley->$column; // This makes sure that for example :pinch: gets translated before :p
        }

        if ($emoticonbar == 0)
        { // don't sort when it's only for use in the emoticonbar
            array_multisort(array_keys($smileyArray), SORT_DESC, $smileyArray);
            reset($smileyArray);
        }

        return $smileyArray;
    }

    function topicToolbar($selected, $tawidth)
    {
        //TO USE
        // $topicToolbar = smile:topicToolbar();
        // echo $topicToolbar;
        //$selected var is used to check the right selected icon
        //important for the edit function
        $selected = (int)$selected;
?>

<table border="0" cellspacing="0" cellpadding="0" class="kflat">
	<tr>
		<td><input type="radio" name="topic_emoticon" value="0"
			<?php echo $selected==0?" checked=\"checked\" ":"";?> /><?php @print(_NO_SMILIE); ?>

		<input type="radio" name="topic_emoticon" value="1"
			<?php echo $selected==1?" checked=\"checked\" ":"";?> /> <img
			src="<?php echo KUNENA_URLEMOTIONSPATH ;?>exclam.gif" alt=""
			border="0" /> <input type="radio" name="topic_emoticon" value="2"
			<?php echo $selected==2?" checked=\"checked\" ":"";?> /> <img
			src="<?php echo KUNENA_URLEMOTIONSPATH ;?>question.gif" alt=""
			border="0" /> <input type="radio" name="topic_emoticon" value="3"
			<?php echo $selected==3?" checked=\"checked\" ":"";?> /> <img
			src="<?php echo KUNENA_URLEMOTIONSPATH ;?>arrow.gif" alt=""
			border="0" /> <?php
            if ($tawidth <= 320) {
                echo '</tr><tr>';
            }
            ?>

		<input type="radio" name="topic_emoticon" value="4"
			<?php echo $selected==4?" checked=\"checked\" ":"";?> /> <img
			src="<?php echo KUNENA_URLEMOTIONSPATH ;?>love.gif" alt="" border="0" />

		<input type="radio" name="topic_emoticon" value="5"
			<?php echo $selected==5?" checked=\"checked\" ":"";?> /> <img
			src="<?php echo KUNENA_URLEMOTIONSPATH ;?>grin.gif" alt="" border="0" />

		<input type="radio" name="topic_emoticon" value="6"
			<?php echo $selected==6?" checked=\"checked\" ":"";?> /> <img
			src="<?php echo KUNENA_URLEMOTIONSPATH ;?>shock.gif" alt=""
			border="0" /> <input type="radio" name="topic_emoticon" value="7"
			<?php echo $selected==7?" checked=\"checked\" ":"";?> /> <img
			src="<?php echo KUNENA_URLEMOTIONSPATH ;?>smile.gif" alt=""
			border="0" /></td>
	</tr>
</table>

<?php
    }

    function purify($text)
    {
        $text = preg_replace("'<script[^>]*>.*?</script>'si", "", $text);
        $text = preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is', '\2 (\1)', $text);
        $text = preg_replace('/<!--.+?-->/', '', $text);
        $text = preg_replace('/{.+?}/', '', $text);
        $text = preg_replace('/&nbsp;/', ' ', $text);
        $text = preg_replace('/&amp;/', ' ', $text);
        $text = preg_replace('/&quot;/', ' ', $text);
        //smilies
        $text = preg_replace('/:laugh:/', ':-D', $text);
        $text = preg_replace('/:angry:/', ' ', $text);
        $text = preg_replace('/:mad:/', ' ', $text);
        $text = preg_replace('/:unsure:/', ' ', $text);
        $text = preg_replace('/:ohmy:/', ':-O', $text);
        $text = preg_replace('/:blink:/', ' ', $text);
        $text = preg_replace('/:huh:/', ' ', $text);
        $text = preg_replace('/:dry:/', ' ', $text);
        $text = preg_replace('/:lol:/', ':-))', $text);
        $text = preg_replace('/:money:/', ' ', $text);
        $text = preg_replace('/:rolleyes:/', ' ', $text);
        $text = preg_replace('/:woohoo:/', ' ', $text);
        $text = preg_replace('/:cheer:/', ' ', $text);
        $text = preg_replace('/:silly:/', ' ', $text);
        $text = preg_replace('/:blush:/', ' ', $text);
        $text = preg_replace('/:kiss:/', ' ', $text);
        $text = preg_replace('/:side:/', ' ', $text);
        $text = preg_replace('/:evil:/', ' ', $text);
        $text = preg_replace('/:whistle:/', ' ', $text);
        $text = preg_replace('/:pinch:/', ' ', $text);
        //bbcode
        $text = preg_replace('/\[hide==([1-3])\](.*?)\[\/hide\]/s', '', $text);
        $text = preg_replace('/(\[b\])/', ' ', $text);
        $text = preg_replace('/(\[\/b\])/', ' ', $text);
        $text = preg_replace('/(\[s\])/', ' ', $text);
        $text = preg_replace('/(\[\/s\])/', ' ', $text);
        $text = preg_replace('/(\[i\])/', ' ', $text);
        $text = preg_replace('/(\[\/i\])/', ' ', $text);
        $text = preg_replace('/(\[u\])/', ' ', $text);
        $text = preg_replace('/(\[\/u\])/', ' ', $text);
        $text = preg_replace('/(\[quote\])/', ' ', $text);
        $text = preg_replace('/(\[\/quote\])/', ' ', $text);
        $text = preg_replace('/(\[strike\])/', ' ', $text);
        $text = preg_replace('/(\[\/strike\])/', ' ', $text);
        $text = preg_replace('/(\[sub\])/', ' ', $text);
        $text = preg_replace('/(\[\/sub\])/', ' ', $text);
        $text = preg_replace('/(\[sup\])/', ' ', $text);
        $text = preg_replace('/(\[\/sup\])/', ' ', $text);
        $text = preg_replace('/(\[left\])/', ' ', $text);
        $text = preg_replace('/(\[\/left\])/', ' ', $text);
        $text = preg_replace('/(\[center\])/', ' ', $text);
        $text = preg_replace('/(\[\/center\])/', ' ', $text);
        $text = preg_replace('/(\[right\])/', ' ', $text);
        $text = preg_replace('/(\[\/right\])/', ' ', $text);
        $text = preg_replace('/(\[code:1\])(.*?)(\[\/code:1\])/', '\\2', $text);
        $text = preg_replace('/(\[ul\])(.*?)(\[\/ul\])/s', '\\2', $text);
        $text = preg_replace('/(\[li\])(.*?)(\[\/li\])/s', '\\2', $text);
        $text = preg_replace('/(\[ol\])(.*?)(\[\/ol\])/s', '\\2', $text);
        $text = preg_replace('/\[img size=([0-9][0-9][0-9])\](.*?)\[\/img\]/s', '\\2', $text);
        $text = preg_replace('/\[img size=([0-9][0-9])\](.*?)\[\/img\]/s', '\\2', $text);
        $text = preg_replace('/\[img\](.*?)\[\/img\]/s', '\\1', $text);
        $text = preg_replace('/\[url\](.*?)\[\/url\]/s', '\\1', $text);
        $text = preg_replace('/\[url=(.*?)\](.*?)\[\/url\]/s', '\\2 (\\1)', $text);
        $text = preg_replace('/<A (.*)>(.*)<\/A>/i', '\\2', $text);
        $text = preg_replace('/\[file(.*?)\](.*?)\[\/file\]/s', '\\2', $text);
        $text = preg_replace('/\[hide(.*?)\](.*?)\[\/hide\]/s', ' ', $text);
        $text = preg_replace('/\[spoiler(.*?)\](.*?)\[\/spoiler\]/s', ' ', $text);
        $text = preg_replace('/\[size=([1-7])\](.+?)\[\/size\]/s', '\\2', $text);
        $text = preg_replace('/\[color=(.*?)\](.*?)\[\/color\]/s', '\\2', $text);
        $text = preg_replace('/\[video\](.*?)\[\/video\]/s', '\\1', $text);
        $text = preg_replace('/\[ebay\](.*?)\[\/ebay\]/s', '\\1', $text);
        $text = preg_replace('#/n#s', ' ', $text);
        $text = strip_tags($text);
        //$text = stripslashes(kunena_htmlspecialchars($text));
        $text = stripslashes($text);
        return ($text);
    } //purify
}
