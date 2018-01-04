<?php

namespace app\commands;

use app\models\Contact;
use yii\console\Controller;
use yii\helpers\Console;

class TestController extends Controller
{
    public function actionCreate()
    {
        $neo = new Contact();
        $neo->name = 'Neo';
        if ($neo->save()) {
            $this->stdout("Neo model created.\n", Console::FG_GREEN);
        } else {
            $this->stdout("Neo model create failed.\n", Console::FG_RED);
        }
    }

    public function actionFind()
    {
        $query = Contact::find();
        $neo = $query->andWhere(['name' => 'Neo'])->one();
        $this->stdout("Use find() query: " . var_export((bool)$neo, true) . "\n");

        $query = Contact::findWithTrashed();
        $neo = $query->andWhere(['name' => 'Neo'])->one();
        $this->stdout("Use findWithTrashed() query: " . var_export((bool)$neo, true) . "\n");
    }

    public function actionDelete()
    {
        if ( ! ($neo = Contact::findOne(['name' => 'Neo']))) {
            $this->stdout("Neo model not found.\n", Console::FG_RED);
            return;
        }

        if ($neo->delete()) {
            $this->stdout("Neo model deleted.\n", Console::FG_GREEN);
        } else {
            $this->stdout("Neo model delete failed.\n", Console::FG_RED);
        }
    }

    public function actionRestore()
    {
        if ( ! ($neo = Contact::findOneWithTrashed(['name' => 'Neo']))) {
            $this->stdout("Neo model not found.\n", Console::FG_RED);
            return;
        }

        if ($neo->restore()) {
            $this->stdout("Neo model restored.\n", Console::FG_GREEN);
        } else {
            $this->stdout("Neo model restore failed.\n", Console::FG_RED);
        }
    }
}