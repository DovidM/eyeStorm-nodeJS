<?php

require_once(__DIR__ . '/../../../../vendor/autoload.php');
require_once(__DIR__ . '/../helpers.php');

class RecoverTest extends RecoverPasswordHelper {

    /**
     * Sends graphql query to generate new password
     *
     * @param $email - email of user who wants a new password
     * @param $authCode - auth code sent when doing 2fa or when verifying email - whichever is most recent
     * @param $username - username of user
     *
     * @return graphql data
     */
    protected function helpQuery(string $email, string $authCode, string $username) {

        return $this->request([
            'query' => 'mutation recoverPassword($email: String!, $authCode: String!, $username: String!) {
                            recoverPassword(email: $email, authCode: $authCode, username: $username) {
                                message
                            }
                        }',
            'variables' => [
                'email' => $email,
                'authCode' => $authCode,
                'username' => $username
            ]
        ])['recoverPassword'];
    }

    /**
     * Checks if password was changed
     *
     * @param $user - GenerateRows->users element (with original password)
     *
     * @return boolean if password changed or not
     */
    protected function helpCheckPasswordChanged(array $user) {

        $dbPassword = Db::query("SELECT password FROM users WHERE email = ?", [$user['email']])->fetchColumn();

        return !password_verify($user['password'], $dbPassword);
    }

    /**
     *
     * @param $fieldToTest - index of GenerateRows->user element
     *
     * @return ['user' => random_element_of_GenerateRows->users, 'fieldToTest' => incorrect_value_of_$fieldToTest]
     */
    protected function helpGetBadData(string $fieldToTest) {

        $faker = TestHelper::faker();

        $user = $faker->randomElement($this->Database->GenerateRows->users);
        $badValue = $user[$fieldToTest] . $faker->word();

        return ['user' => $user, 'fieldToTest' => $badValue];
    }

    function testBadIncorrectUsername() {

        $randomUser = $this->helpGetBadData('username');

        $data = $this->helpQuery($randomUser['user']['email'], $randomUser['user']['auth'], $randomUser['fieldToTest']);

        $this->assertNull($data); // response subject to change

        $this->assertFalse($this->helpCheckPasswordChanged($randomUser['user']));
    }

    function testBadIncorrectEmail() {

        $randomUser = $this->helpGetBadData('email');

        $data = $this->helpQuery($randomUser['fieldToTest'], $randomUser['user']['auth'], $randomUser['user']['username']);

        $this->assertNull($data); // response subject to change

        $this->assertFalse($this->helpCheckPasswordChanged($randomUser['user']));
    }

    function testBadIncorrectAuthCode() {

        $randomUser = $this->helpGetBadData('auth');

        $data = $this->helpQuery($randomUser['user']['email'], $randomUser['fieldToTest'], $randomUser['user']['username']);

        $this->assertNull($data); // response subject to change

        $this->assertFalse($this->helpCheckPasswordChanged($randomUser['user']));
    }

    function testGood() {

        $randomUser = $this->helpGetBadData('auth')['user']; // auth is not relevant to this test, but need to pass a param

        $data = $this->helpQuery($randomUser['email'], $randomUser['auth'], $randomUser['username']);

        $this->assertNotNull($data);

        $this->assertTrue($this->helpCheckPasswordChanged($randomUser));
    }
}
?>