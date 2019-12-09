<?php

public function Allcomments($date_from, $date_to, $search_param, $limit)
{        
    $userId = Yii::$app->user->id;
        
    $insta_user_idArr = ProfileLink::find()->select('profile_link.insta_user_id')->innerJoin('events_users', '`events_users`.`user_id` = `profile_link`.`insta_user_id`')->where(['profile_link.user_id'=>$userId, 'profile_link.type_subscription'=>['subscription', 'my_subscription'], 'events_users.comments'=>1])->asArray()->all();
        
    $instaUserIdArr = [];
    foreach($insta_user_idArr as $insta_user_id){
        $instaUserIdArr[] = $insta_user_id['insta_user_id'];
    }      
        
    $insta_user_nickname = InstagramUsers::find()->select('nickname')->where(['in', 'id', $instaUserIdArr])->asArray()->all();
        
    $nicknameArr = [];
    
    foreach($insta_user_nickname as $nickname){
        $nicknameArr[] = $nickname['nickname']; 
    }       
        
    $query1 = new \yii\db\Query(); 
    $InstaUsersId = $query1->select(['id'])->from('comments')->where(['in', 'nickname_to', $nicknameArr])->orderBy(['date_comment'=>SORT_DESC])->LIMIT($limit)->all();
        
    if(empty($search_param)){
        $query2 = new \yii\db\Query();
        $regexpData = $query2->select(['*'])->from('comments')->where(['id'=>$InstaUsersId])->LIMIT($limit)->all(); 
    }else{
        $query2 = new \yii\db\Query();
        $regexpData = $query2->select(['*'])->from('comments')->where(['REGEXP', 'description',"$search_param"])->andWhere(['id'=>$InstaUsersId])->LIMIT($limit)->all(); 
        }
            
    $commentsArr = [];
    $totalCommentsArr = [];
    foreach($regexpData as $commentItem){           
        // плюс фильтрация по ключевым словам в комментарии           
        $postModel = Posts::findOne($commentItem['post_xml_id']);     
            
        $commentsArr['commentator_profile'] = $commentItem['nickname_from'];
        $commentsArr['postPhoto'] = $postModel->photo_url;
        $commentsArr['date_comment'] = $commentItem['date_comment'];
        $commentsArr['text_comment'] = $commentItem['description'];
        $commentsArr['post_url'] = 'https://www.instagram.com/p/' . $postModel->post_xml_id . '/'; 
        $commentsArr['commentator_url'] = 'https://www.instagram.com/' . $commentItem["nickname_from"] . '/';
            
        $commentator_photo = InstagramUsers::find()->where(['nickname'=>$commentItem['nickname_from']])->one();
        $commentator_photo = $commentator_photo->user_photo;
            
        $commentsArr['commentator_photo'] = $commentator_photo;
            
        $totalCommentsArr[] = $commentsArr;
            
    }        
    return $totalCommentsArr;       
}