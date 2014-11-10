#coding: utf8
import sys
import os

COURSE_NUM_PER_LINE = 4

course_list = [
("公共课",'''
政治 (95)
英语 (80)
英语 (7)
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

course_list_dict = list()

region_cnt = 0
for region, course_str in course_list:
    region_cnt += 1

    course_dict = dict()
    course_dict['id'] = region_cnt
    course_dict['region'] = unicode(region, "utf-8")
    course_dict['course'] = list()

    courses = [unicode(course.strip().split(' ')[0].strip(), "utf-8") for course in course_str.strip().split('\n') ]
    course_cnt = 0;
    for course in courses:
        course_cnt += 1
        course_dict['course'].append({"id":"C"+str(region_cnt * 100000 + course_cnt), "name" : course})

    course_list_dict.append(course_dict)


panel = ""
for course_dict in course_list_dict:
    panel += '''<div class="panel panel-info"><div class="panel-heading">''' + course_dict['region'] + '''</div><div class="panel-body"><table class="table">'''

    course_cnt = 0
    for course in course_dict['course']:
        if course_cnt % COURSE_NUM_PER_LINE == 0:
            panel += "<tr>"
        panel += '''<td width="''' + str(99.0 / COURSE_NUM_PER_LINE) + '''%"><a href="{SITE_URL}?category/view/''' + course['id'] + '''">''' + course['name'] + '''</a></td>'''

        course_cnt += 1
        if course_cnt % COURSE_NUM_PER_LINE == 0:
            panel += "</tr>"

    if course_cnt % COURSE_NUM_PER_LINE != 0:
        panel += "</tr>"
    panel = panel + '''</table></div></div>'''

print panel.encode('utf8')
print '\n\n\n'


sql = ""
for course_dict in course_list_dict:
    for course in course_dict['course']:
        sql += "INSERT INTO category VALUES ('%s','%s','%s');\n" % (course['id'], course['name'], course_dict['region'])

print sql.encode('utf8')
