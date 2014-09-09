<? !defined('IN_SITE') && exit('Access Denied'); $setting=$this->setting; $user=$this->user;  ?><!--相关已解决--><? if(isset($userinfo)) { ?><div style="width:650px;">
    <div class="row">
        <div class="col-md-2">
            <a href="<?=SITE_URL?>?u-<?=$userinfo['uid']?>.html">
                <img src="<?=$userinfo['avatar']?>" class="img-circle" alt="<?=$userinfo['username']?>"/>
            </a>
        </div>
        <div class="col-md-10">
            <div>
                <a href="<?=SITE_URL?>?u-<?=$userinfo['uid']?>.html"><? echo cutstr($userinfo['username'],24,''); ?></a>
                <? if($userinfo['gender']!=2) { ?>                    <span class="icon_<?=$userinfo['gender']?>"></span>
                <? } if($userinfo['expert']) { ?>                    <span class="icon_expert" title='专家'></span>
                <? } ?>            </div>
            <div>
                <? if($userinfo['signature']) { ?>                <h5 class="info"><?=$userinfo['signature']?></h5>
                <? } ?>                <? if($userskill) { ?>                <h5 class="info"><span style="font-weight:bold">擅长:</span>&nbsp;&nbsp;
                    
<? if(is_array($userskill)) { foreach($userskill as $skill) { ?>
                        <span class="btn btn-link"><?=$skill?></span>
                    
<? } } ?>
                </h5>
                <? } ?>                
<? if(is_array($education)) { foreach($education as $edu) { ?>
                <span style="font-weight:bold">
                    <? if($edu['edu_type'] == HIGH_SCHOOL) { ?> 高中：
                    <? } elseif($edu['edu_type'] == BACHELOR) { ?> 本科：
                    <? } elseif($edu['edu_type'] == MASTER) { ?> 硕士：
                    <? } elseif($edu['edu_type'] == DOCTOR) { ?> 博士：
                    <? } elseif($edu['edu_type'] == POST_DOCTOR) { ?> 博士后：
                    <? } else { ?> 经历： <? } ?>                </span>
                <?=$edu['school']?>&nbsp;&nbsp;<?=$edu['department']?>&nbsp;&nbsp;<?=$edu['major']?>&nbsp;&nbsp;&nbsp;&nbsp;<?=$edu['start_time']?>&nbsp;至&nbsp;<?=$edu['end_time']?><br/>
                
<? } } ?>
            </div>
            <h4 style="font-size:11px;">成功抢单<?=$userinfo['solved']?>个</h4>
        </div><!-- col-md-9 right end -->
        <div class="col-md-12">
            <? if($userresume['experience']) { ?>            <h6><?=$userresume['experience']?></h6> 
            <? } ?>        </div>
    </div>
</div><? } ?>