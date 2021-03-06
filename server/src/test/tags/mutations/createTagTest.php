<?php

require_once(__DIR__ . '/../../../../vendor/autoload.php');
require_once(__DIR__ . '/../helpers.php');

class CreateTagTest extends AllTagsTestHelper {

    /**
     * Sends mutation to server to add tag
     *
     * @param $tag - tag to add
     * @param $user - user who will be adding the tag
     */
    protected function helpTest(string $tag, array $user) {

        return $this->request([
            'query' => 'mutation createTag($tag: String!) {
                            createTag(tag: $tag) {
                                tag
                            }
                        }',
            'variables' => [
                'tag' => $tag
            ]
        ], TestHelper::getJwt($user))['createTag'];
    }

    private function getUserWhoCanAddTag() {

        return TestHelper::searchArray($this->Database->GenerateRows->users, function (array $currentUser) {
            return $currentUser['level'] > 1;
        });
    }

    function testGoodLevelTwoPlusCanAddTag() {

        $user = $this->getUserWhoCanAddTag();

        $tag = $this->Database->GenerateRows->tag_list()['tag'];

        $data = $this->helpTest($tag, $user);

        $this->assertEquals($tag, strtolower($data['tag']));
    }

    function testCannotCreateEmptyTag() {

        $user = $this->getUserWhoCanAddTag();

        $tag = '';
        $data = $this->helpTest($tag, $user);

        $dbTag = Db::query("SELECT tag FROM tag_list WHERE tag = ?", [$tag])->fetchColumn();

        $this->assertFalse($dbTag);
    }

    function testMaliciousDataNotAccepted() {

        $user = TestHelper::searchArray($this->Database->GenerateRows->users, function (array $currentUser) {
            return $currentUser['level'] > 2;
        });

        foreach (TestHelper::$unsafeData as $tag) {

            $data = $this->helpTest($tag, $user);

            $this->assertTrue(!$data['tag']);
            $dbTag = Db::query("SELECT tag FROM tag_list WHERE tag = ?", [$tag])->fetchColumn();

            $this->assertFalse($dbTag);
        }
    }
}
?>
