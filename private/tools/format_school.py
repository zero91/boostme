#coding: utf8
import sys
import os

NUM_PER_LINE = 4

school_list = [
("北京考研院校", ''' 清华大学 (16)
北京大学 优秀 (66)
中国人民大学 优秀 (145)
中国政法大学 (31)
北京航空航天大学 (25)
中央财经大学 (48)
北京邮电大学 优秀 (15)
中国传媒大学 (61)
北京师范大学 (29)
北京化工大学 (21)
北京理工大学 (38)
北京交通大学 优秀 (94)
中国地质大学(北京) 优秀 (123)
中央民族大学 (3)
北京工业大学 (36)
北京外国语大学 (13)
首都经济贸易大学 优秀 (20)
中国人民公安大学 (46)
北京科技大学 (20)
首都师范大学 (30)
对外经济贸易大学 优秀 (65)
华北电力大学(北京) 优秀 (16)
中国石油大学(北京) 优秀 (21)
中国矿业大学(北京) (9)
北京电影学院 (5)
北京工商大学 (34)
中国农业大学 (21)
北京林业大学 (13)
中央音乐学院
北京中医药大学 (3)
北京体育大学
北京语言大学 (5)
首都医科大学 (5)
财政部财政科学研究所 (17)
中国科学院 (13)
中国社会科学院 (4)
中国农业科学院 (2)
中国林业科学研究院
中央戏剧学院 (1)
北方工业大学
北京信息科技大学 '''),

("上海考研院校",  '''复旦大学 (64)
上海交通大学 优秀 (22)
同济大学 优秀 (30)
华东师范大学 优秀 (62)
上海财经大学 (67)
华东理工大学 优秀 (39)
上海外国语大学 (1)
华东政法大学 优秀 (10)
上海大学 优秀 (49)
上海理工大学 优秀 (32)
东华大学 (21)
上海师范大学 优秀 (11)
上海海事大学 (9)
第二军医大学 (5)
上海体育学院
上海中医药大学 (1)
上海对外经贸大学 (7) '''),

("华北考研院校（天津 | 河北 | 内蒙古 | 山西）" , '''南开大学 优秀 (59)
天津大学 优秀 (114)
天津医科大学 (1)
天津财经大学 (24)
天津师范大学 (17)
天津科技大学 (1)
天津工业大学 (4)
天津商业大学 优秀 (3)
河北工业大学 (12)
华北电力大学（保定） (23)
燕山大学 优秀 (7)
河北大学 (24)
河北师范大学 (10)
山西师范大学 (3)
山西财经大学 (18)
山西大学 (2)
太原理工大学 优秀 (41)
中北大学 (1)
内蒙古大学 (9)
内蒙古师范大学 (7)
内蒙古工业大学 '''),

("东北考研院校（辽宁 | 吉林 | 黑龙江）" , ''' 东北大学 优秀 (94)
大连理工大学 优秀 (56)
东北财经大学 (76)
大连海事大学 优秀 (37)
辽宁师范大学 (7)
辽宁大学 (17)
沈阳工业大学 (4)
辽宁工程技术大学
沈阳师范大学 (2)
沈阳药科大学 (4)
中国医科大学 (8)
大连交通大学
吉林大学 (46)
东北师范大学 优秀 (58)
延边大学
长春理工大学
哈尔滨工业大学 (27)
哈尔滨工程大学 (14)
黑龙江大学
东北农业大学
东北林业大学 (1)
哈尔滨师范大学 (2)
哈尔滨理工大学 (1)
东北石油大学 (1)
大连工业大学 '''),

("华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）" , ''' 山东大学 优秀 (28)
中国海洋大学 (43)
中国石油大学(华东) (17)
山东师范大学 (19)
青岛大学 (10)
山东农业大学 (2)
山东科技大学 (9)
曲阜师范大学 (10)
青岛科技大学
山东财经大学 (1)
南京大学 优秀 (97)
东南大学 优秀 (33)
南京航空航天大学 优秀 (77)
南京师范大学 优秀 (30)
南京理工大学 (13)
苏州大学 (39)
河海大学 (50)
中国矿业大学 (44)
南京邮电大学 优秀 (20)
中国药科大学 (4)
南京信息工程大学
江苏大学 优秀 (6)
江南大学 (28)
扬州大学 (8)
南京农业大学 (32)
南京工业大学 (8)
浙江大学 (57)
浙江工业大学 (28)
宁波大学 (3)
浙江师范大学 (12)
南京财经大学 (6)
浙江理工大学 (5)
杭州电子科技大学 (7)
浙江工商大学 (18)
杭州师范大学 (10)
浙江财经大学 优秀 (5)
厦门大学 优秀 (81)
福州大学 优秀 (21)
福建师范大学 优秀 (32)
福建农林大学
华侨大学 (3)
中国科学技术大学 优秀 (10)
合肥工业大学 (28)
安徽大学 优秀 (30)
安徽师范大学 优秀 (11)
安徽理工大学 (2)
南昌大学 (20)
江西师范大学 (17)
江西财经大学 (39)
华东交通大学 '''),

("华中考研院校（河南 | 湖北 | 湖南 ）" , ''' 郑州大学 (11)
河南大学 (16)
河南师范大学 优秀 (4)
河南理工大学 (2)
中南民族大学
武汉大学 优秀 (82)
华中科技大学 (63)
华中师范大学 优秀 (55)
武汉理工大学 优秀 (40)
中南财经政法大学 优秀 (31)
湖北大学 (4)
中国地质大学(武汉) 优秀 (8)
华中农业大学 (29)
武汉科技大学 (3)
长江大学
湖南大学 优秀 (44)
中南大学 (16)
湖南师范大学 (19)
湘潭大学 (1)
国防科技大学 (3)
长沙理工大学 (10)
南华大学 (20)
湖南工业大学 (1)
吉首大学'''),

("华南考研院校（广东|广西|海南）" , ''' 广州大学 优秀 (3)
中山大学 优秀 (17)
华南理工大学 优秀 (45)
华南师范大学 优秀 (50)
暨南大学 (63)
深圳大学 优秀 (16)
广东工业大学 (10)
广东外语外贸大学 优秀 (46)
汕头大学
华南农业大学 (2)
南方医科大学 (6)
广州中医药大学 (5)
广西大学 优秀 (35)
桂林电子科技大学 (5)
广西师范大学 优秀 (3)
海南大学 优秀 (13)'''),

("西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）" , ''' 西安交通大学 (31)
西安电子科技大学 (12)
长安大学 优秀 (48)
陕西师范大学 优秀 (34)
西北大学 优秀 (82)
西北工业大学 (28)
西安建筑科技大学 优秀 (32)
西北农林科技大学 (23)
西安理工大学 (11)
西安科技大学 (3)
陕西科技大学 (39)
西北政法大学 (1)
第四军医大学 (4)
兰州大学 (13)
兰州交通大学
西北师范大学 优秀 (4)
兰州理工大学 (1)
青海大学
宁夏大学 (1)
新疆大学 (9)
石河子大学 (4) ''') ,

("西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）" , ''' 四川大学 (43)
西南财经大学 优秀 (79)
电子科技大学 (24)
西南交通大学 优秀 (70)
成都理工大学 优秀 (14)
西南民族大学 优秀 (2)
西南石油大学 (1)
四川师范大学 (5)
四川农业大学 (10)
成都中医药大学
成都信息工程学院
重庆大学 优秀 (80)
重庆邮电大学 (11)
西南政法大学 (4)
重庆工商大学 (1)
重庆师范大学
重庆交通大学 (3)
西南大学 (23)
云南大学 优秀 (17)
昆明理工大学 (6)
云南师范大学 (9)
云南民族大学
贵州大学 (3)
贵州师范大学 (3)
西藏大学 (2) ''')
]

course_list = [
("公共课",'''
政治 (95)
英语1 (80)
英语2 (7)
数学 (158) '''),

("统考/联考科目",'''
计算机与软件 (7)
历史学 优秀 (46)
医学 (15)
教育学 优秀 (39)
心理学 (60)
农学 (12)
日语 优秀 (6)
俄语
'''),

("专业课",'''
哲学 (4)
经济学 (39)
金融学 (52)
国际贸易 (3)
法学 优秀 (15)
政治学 (4)
社会学 (11)
民族学
马克思主义理论 (2)
体育学 (1)
中国语言文学 (50)
英语语言文学 (22)
外国语言文学 (3)
新闻传播学 (21)
数学专业 (13)
物理学 (7)
化学与化工 优秀 (6)
天文学
地理学
大气科学
海洋科学
地球物理学
地学与地质 (1)
生物学 (1)
系统科学
生态学
力学 (1)
材料科学与工程 (4)
电子与信息 优秀 (29)
计算机科学与技术
基础医学
公共卫生与预防医学
药学/中药学 (9)
医学技术/护理学 (7)
机械工程 优秀 (12)
车辆工程
光学工程 (5)
仪器科学与技术
冶金工程
动力工程及工程热物理 (2)
电气工程 优秀 (22)
控制科学/自动化 优秀 (11)
土木工程/建筑学 优秀 (19)
水利工程
测绘科学与技术
矿业工程
石油与天然气工程
纺织科学与工程
轻工技术与工程
交通运输工程 (5)
船舶与海洋工程
航空宇航科学与技术
兵器科学与技术
核科学与技术
农业工程/林业工程 (1)
环境科学与工程 (2)
生物医学工程
食品科学与工程 (3)
城乡规划/风景园林
软件工程 (3)
生物工程
管理科学与工程 (20)
工学（其他）
保险与精算
环境与市政 (1)
管理学 优秀 (16)
会计学 (7)
工商管理 (10)
公共管理 (3)
图书情报与档案管理
农林经济管理
艺术学 (13)''')
]


def calc_course(course_list):
    course_list_dict = list()
    region_cnt = 0

    for region, course_str in course_list:
        region_cnt += 1
        course_dict = dict()
        course_dict['id'] = region_cnt
        course_dict['region'] = unicode(region, "utf-8")
        course_dict['item'] = list()

        courses = [unicode(course.strip().split(' ')[0].strip(), "utf-8") for course in course_str.strip().split('\n') ]
        course_cnt = 0;
        for course in courses:
            course_cnt += 1
            course_dict['item'].append({"id":"C"+str(region_cnt * 100000 + course_cnt), "name" : course})

        course_list_dict.append(course_dict)
    panel = ""
    for course_dict in course_list_dict:
        panel += '''<div class="panel panel-info"><div class="panel-heading">''' + course_dict['region'] + '''</div><div class="panel-body"><table class="table">'''

        course_cnt = 0
        for course in course_dict['item']:
            if course_cnt % NUM_PER_LINE == 0:
                panel += "<tr>"
            panel += '''<td width="''' + str(99.0 / NUM_PER_LINE) + '''%"><a href="{SITE_URL}?category/view/''' + course['id'] + '''">''' + course['name'] + '''</a></td>'''

            course_cnt += 1
            if course_cnt % NUM_PER_LINE == 0:
                panel += "</tr>"

        if course_cnt % NUM_PER_LINE != 0:
            panel += "</tr>"
        panel = panel + '''</table></div></div>'''

    sql = ""
    for course_dict in course_list_dict:
        for course in course_dict['item']:
            sql += "INSERT INTO category VALUES ('%s','%s','%s');\n" % (course['id'], course['name'], course_dict['region'])

    return (course_list_dict, panel, sql)

def calc_school(school_list):
    school_list_dict = list() 
    region_cnt = 0
    for region, school_str in school_list:
        region_cnt += 1

        school_dict = dict()
        school_dict['id'] = region_cnt
        school_dict['region'] = unicode(region, "utf-8")
        school_dict['item'] = list()

        schools = [unicode(school.strip().split(' ')[0].strip(), "utf-8") for school in school_str.strip().split('\n') ]
        school_cnt = 0;
        for school in schools:
            school_cnt += 1
            school_dict['item'].append({"id":"S"+str(region_cnt * 100000 + school_cnt), "name" : school}) 
        school_list_dict.append(school_dict) 

    panel = ""
    for school_dict in school_list_dict:
        panel += '''<div class="panel panel-info"><div class="panel-heading">''' + school_dict['region'] + '''</div><div class="panel-body"><table class="table">'''
        school_cnt = 0
        for school in school_dict['item']:
            if school_cnt % NUM_PER_LINE == 0:
                panel += "<tr>"
            panel += '''<td><a href="{SITE_URL}?category/view/''' + school['id'] + '''">''' + school['name'] + '''</a></td>''' 
            school_cnt += 1
            if school_cnt % NUM_PER_LINE == 0:
                panel += "</tr>"

        if school_cnt % NUM_PER_LINE != 0:
            panel += "</tr>"
        panel = panel + '''</table></div></div>'''

    sql = ""
    for school_dict in school_list_dict:
        for school in school_dict['item']:
            sql += "INSERT INTO category VALUES ('%s','%s','%s');\n" % (school['id'], school['name'], school_dict['region'])

    return (school_list_dict, panel, sql)

if __name__ == '__main__':
    (course_list_dict, course_panel, sql) = calc_course(course_list)
    (school_list_dict, school_panel, sql) = calc_school(school_list)

    js_dict = dict()
    js_dict["考研院校".decode('utf-8')] = school_list_dict
    js_dict["考研课程".decode('utf-8')] = course_list_dict
    #print js_dict

    print course_panel.encode('utf-8')
    print '\n\n\n\n\n\n'
    print school_panel.encode('utf-8')

