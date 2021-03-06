<?php

require_once(__DIR__ . '/../../../../vendor/autoload.php');
require_once(__DIR__ . '/../helpers.php');

class NeutralUserCommentTest extends CommentTestHelper {

    /**
     * Sends graphql query
     *
     * @param $variableTypes - graphql variables with type. Example: ['$num: => 'ID', '$limit': 'Int']
     * @param $variableValues - values to give variables listed as keys to $variableTypes
     */
    protected function helpTestArgs(array $variableTypes, array $variableValues) {

        $variableStrings = TestHelper::convertVariableArrayToGraphql($variableTypes);

        return $this->request([
            'query' => "query CommentQuery({$variableStrings['types']}) {
                            comments({$variableStrings['mappings']}) {
                                id
                                content,
                                dateCreated,
                                canDelete
                            }
                        }",
            'variables' => $variableValues
        ]);
    }

    function testCanQueryById() {

        $comment = $this->helpGetComment(true);

        $data = $this->helpTestArgs(['$id' => 'ID'], ['id' => $comment['id']]);

        $this->assertNotNull($data['comments']);
    }

    function testCanQueryByAuthorId() {

        $comment = $this->helpGetComment(true);

        $data = $this->helpTestArgs(['$authorid' => 'ID'], ['authorid' => $comment['authorid']]);

        $this->assertNotNull($data['comments']);
    }

    function testCanQueryByArtId() {

        $comment = $this->helpGetComment(true);

        $data = $this->helpTestArgs(['$artId' => 'ID'], ['artId' => $comment['art_id']]);

        $this->assertNotNull($data['comments']);
    }
}
?>