<?php
   $string = <<<EOS
<page>
<talentTrees>
<tree name="Football" order="2" />
<tree name="Baseball" order="0" />
<tree name="Frisbee" order="1" />
</talentTrees>
</page>
EOS;

$xml = simplexml_load_string($string);

$trees = $xml->xpath('/page/talentTrees/tree');
function sort_trees($t1, $t2) {
    return strcmp($t1['order'], $t2['order']);
}

usort($trees, 'sort_trees');
var_dump($trees);

?>