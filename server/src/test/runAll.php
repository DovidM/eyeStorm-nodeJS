<?php

$filesToTest = [
    'articles/mutations/createArticleTest.php',
    'articles/mutations/deleteArticleTest.php',
    'articles/mutations/updateArticleTest.php',
    'articles/queries/UserLoggedInTest.php',
    'articles/queries/UserNeutralTest.php',
    'comments/mutations/createComment.php',
    'comments/mutations/deleteComment.php',
    'comments/queries/NeutralUser.php',
    'comments/queries/NotLoggedIn.php',
    'issues/mutations/updateIssue.php',
    'issues/queries/UserNeutral.php'
];

echo `clear`;

foreach ($filesToTest as $file) {
    echo "\nTesting " . $file . "\n";
    echo `../../vendor/phpunit/phpunit/phpunit {$file} --stop-on-failure --stop-on-error --stderr`;
}