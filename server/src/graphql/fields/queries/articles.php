<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once(__DIR__ . '/../../types/article.php');


use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\AbstractField;
use Youshido\GraphQL\Config\Field\FieldConfig;
use Youshido\GraphQL\Type\Object\AbstractObjectType;
use Youshido\GraphQL\Type\Scalar\StringType;
use Youshido\GraphQL\Type\Scalar\IntType;
use Youshido\GraphQL\Type\Scalar\IdType;
use Youshido\GraphQL\Type\ListType\ListType;


class ArticlesField extends AbstractField {

    public function build(FieldConfig $config) {

        $config->addArguments([
            'id' => new IdType(),
            'authorid' => new IdType(),
            'tag' => new StringType(),
            'issue' => new IntType(),
            'url' => new StringType(),
        ]);
    }

    public function getType() {
        return new ListType(new ArticleType());
    }

    public function resolve($root, array $args, ResolveInfo $info) {

        return $this->getArticles($args);
    }

    // this is separate from resolve so can call it from types/user.php
    public function getArticles($args) {

        $sanitized = filter_var_array($args, FILTER_SANITIZE_STRING);
        $sanitized = $this->transformArgs($sanitized);

        $where = Db::setPlaceholders($sanitized);
        $where = str_replace('pageinfoId =', 'pageinfo.id =', $where); // sql: can't have a dot in placeholder
        $where = str_replace('tag = :tag', 'pageinfo.id IN (SELECT art_id FROM tags WHERE tag = :tag)', $where);

        if (empty($args)) {

            try {

                Guard::userMustBeLoggedIn();
                $where = 1;
            } catch(Exception $e) {

                $privateIssue = Db::query("SELECT num FROM issues WHERE ispublic = ?", [0])->fetchColumn();
                $where = "issue < {$privateIssue}";
            }
        }

        $userId = Jwt::getField('id');
        $userLevel = +Jwt::getField('level');

        $rows = Db::query("SELECT pageinfo.id AS id, created AS dateCreated, lede, body, url, issue,
          views, display_order AS displayOrder, authorid AS authorId, ispublic,
          (((authorid = :userId OR author.level < :level) AND ispublic = 0) OR :admin) AS canEdit
          FROM pageinfo
          JOIN users AS author ON author.id = authorid
          JOIN issues ON num = pageinfo.issue
          WHERE ({$where}) AND (ispublic OR :userId)",
          array_merge($sanitized, ['userId' => $userId, 'level' => $userLevel, 'admin' => $userLevel > 2]))->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }

    /**
     * Transforms user-given args into arguments that can be used in sql
     */
    private function transformArgs(array $args) {

        if (array_search('id', array_keys($args)) !== false) { // stops ambigious id in sql
            $args['pageinfoId'] = $args['id'];
            unset($args['id']);
        }

        if (isset($args['issue']) && $args['issue'] < 1) {
            unset($args['issue']);
        }

        return $args;
    }
}

?>