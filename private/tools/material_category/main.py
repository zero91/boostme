#coding: utf8
import sys
import os

SCHOOL_NUM_PER_LINE = 5
DEPT_NUM_PER_LINE = 3
MAJOR_NUM_PER_LINE = 4

# 为了使得学校的ID编号一致，需要保持文件中的学校、专业等得出现顺序
def read_school_info_list():
    info_list = list()
    for line in open("school_department_major.txt", "r").xreadlines():
        line = line.strip()
        if line == '':
            continue

        cells = line.split('\t')

        # region
        region_len = len(info_list)
        if region_len == 0 or info_list[region_len - 1][0] != cells[0]:
            info_list.append([cells[0], list()])

        # school
        school_list = info_list[len(info_list) - 1][1]
        school_len = len(school_list)
        if school_len == 0 or school_list[school_len - 1][0] != cells[1]:
            school_list.append([cells[1], list()])

        # department
        dept_list = school_list[len(school_list) - 1][1]
        dept_len = len(dept_list)
        if dept_len == 0 or dept_list[dept_len - 1][0] != cells[2]:
            dept_list.append([cells[2], cells[3].split(',')])

    return info_list

# 根据文件中读取的学校、专业列表，分别进行编号
def code_school(info_list):
    # 各个不同部分的编号长度, region : 2, school : 4, dept : 3, major : 2
    m_region_dict = dict()
    m_school_dict = dict()
    m_dept_dict = dict()
    m_major_dict = dict()

    for region_index, (region, school_list) in enumerate(info_list):
        region_id = 'R{0:0>2}'.format(region_index + 1)
        m_region_dict[region_id] = {'index' : region_index, 'name' : unicode(region, "utf-8"), 'school_list' : list()}

        for school_index, (school, dept_list) in enumerate(school_list):
            school_id = 'S' + region_id[1:] + '{0:0>4}'.format(school_index + 1)
            m_school_dict[school_id] = {'index' : school_index, 'name' : unicode(school, "utf-8"), 'region' : region_id, 'dept_list' : list()}
            m_region_dict[region_id]['school_list'].append(school_id)

            for dept_index, (dept, major_list) in enumerate(dept_list):
                dept_id = 'D' + school_id[1:] + '{0:0>3}'.format(dept_index + 1)
                m_dept_dict[dept_id] = {'index' : dept_index, 'name' : unicode(dept, "utf-8"), 'school' : school_id, 'region' : region_id, 'major_list' : list()}
                m_school_dict[school_id]['dept_list'].append(dept_id)

                for major_index, major in enumerate(major_list):
                    major_id = 'M' + dept_id[1:] + '{0:0>2}'.format(major_index + 1)
                    m_major_dict[major_id] = {'index' : major_index, 'name' : unicode(major, 'utf-8'), 'dept' : dept_id, 'school' : school_id, 'region' : region_id}
                    m_dept_dict[dept_id]['major_list'].append(major_id)

    return m_region_dict, m_school_dict, m_dept_dict, m_major_dict

if __name__ == '__main__':
    school_info_list = read_school_info_list()

    m_region_dict, m_school_dict, m_dept_dict, m_major_dict = code_school(school_info_list)

    # step1. output school info's js script
    sys.stderr.write("Running Step 1\n")
    school_major_file = open("output/school_major.js", "w")
    school_major_file.write("var m_region_dict = %s;\n" % m_region_dict)
    school_major_file.write("var m_school_dict = %s;\n" % m_school_dict)
    school_major_file.write("var m_dept_dict = %s;\n" % m_dept_dict)
    school_major_file.write("var m_major_dict = %s;\n" % m_major_dict)
    school_major_file.close()


    # step2. output material_category_info table's sql sentence
    sys.stderr.write("Running Step 2\n")
    sql_out_file = open("output/material_category_info.sql", "w")

    sql_prefix = "INSERT INTO material_category_info(`region_id`, `region_name`, `school_id`, `school_name`, `dept_id`, `dept_name`, `major_id`, `major_name`) VALUES"
    category_info_sql = ""
    for region_id, region_dict in m_region_dict.iteritems():
        region = region_dict['name']

        for school_id in region_dict['school_list']:
            school_dict = m_school_dict[school_id]
            school = school_dict['name']

            for dept_id in school_dict['dept_list']:
                dept_dict = m_dept_dict[dept_id]
                dept = dept_dict['name']

                for major_id in dept_dict['major_list']:
                    major = m_major_dict[major_id]['name']
                    sql_out_file.write(("%s('%s','%s','%s','%s','%s','%s','%s','%s');\n" % (sql_prefix, region_id, region, school_id, school, dept_id, dept, major_id, major)).encode("utf-8"))
    sql_out_file.close()

def old():
    school_id_dict = dict()
    for region_id, region_dict in m_region_dict.iteritems():
        for school_id in region_dict['school_list']:
            school_id_dict[m_school_dict[school_id]['name']] = school_id

    panel = ""
    for region, school_list in info_list:
        panel += '<div class="panel panel-info"><div class="panel-heading">' + region + '</div><div class="panel-body"><table class="table" style="font-size:12px;">'
        for index, (school, dept_list) in enumerate(school_list):
            if index % SCHOOL_NUM_PER_LINE == 0:
                panel += "<tr>"

            panel += '<td width="' + str(99.0 / SCHOOL_NUM_PER_LINE) + '%"><a href="{SITE_URL}?material/categorylist/' + school_id_dict[school] +  '">' + school + '</a></td>'

            if index % SCHOOL_NUM_PER_LINE == SCHOOL_NUM_PER_LINE - 1:
                panel += "</tr>"

        if len(school_list) % SCHOOL_NUM_PER_LINE != 0:
            panel += ('<td width="' + str(99.0 / SCHOOL_NUM_PER_LINE) + '%"></td>') * (SCHOOL_NUM_PER_LINE - len(school_list) % SCHOOL_NUM_PER_LINE)
            panel += "</tr>"

        panel = panel + '</table></div></div>'

    dept_panel = ""
    for region_index, (region, school_list) in enumerate(info_list):
        region_id = '{0:0>2}'.format(region_index + 1)

        for school_index, (school, dept_list) in enumerate(school_list):
            school_id = '{0:0>4}'.format(school_index + 1)

            dept_panel += "{if $school_id == " + school_id_dict[school] + "}"
            dept_panel += '<h4><a href="{SITE_URL}?material/categorylist" class="glyphicon glyphicon-backward"></a>&nbsp;&nbsp;' + school + "</h4>"
            for dept_index, (dept, major_list) in enumerate(dept_list):
                dept_id = '{0:0>3}'.format(dept_index + 1)
                dept_panel += '<div class="panel panel-info"><div class="panel-heading">' + dept + '</div><div class="panel-body"><table class="table" style="font-size:12px;">'
                for major_index, major in enumerate(major_list):
                    major_id = '{0:0>2}'.format(major_index + 1)

                    if major_index % MAJOR_NUM_PER_LINE == 0:
                        dept_panel += "<tr>"

                    major_category = "M%s%s%s%s" % (region_id, school_id, dept_id, major_id)

                    dept_panel += '<td width="' + str(99.0 / MAJOR_NUM_PER_LINE) + '%"><a href="{SITE_URL}?material/category/' + major_category +  '">' + major + '</a></td>'

                    if major_index % MAJOR_NUM_PER_LINE == MAJOR_NUM_PER_LINE - 1:
                        dept_panel += "</tr>"

                if len(major_list) % MAJOR_NUM_PER_LINE != 0:
                    dept_panel += ('<td width="' + str(99.0 / MAJOR_NUM_PER_LINE) + '%"></td>') * (MAJOR_NUM_PER_LINE - len(major_list) % MAJOR_NUM_PER_LINE)
                    dept_panel += "</tr>"
                dept_panel = dept_panel + '</table></div></div>'

            dept_panel += "{/if}"


    new_dept_panel = ""
    for region_index, (region, school_list) in enumerate(info_list):
        region_id = '{0:0>2}'.format(region_index + 1)

        for school_index, (school, dept_list) in enumerate(school_list):
            school_id = '{0:0>4}'.format(school_index + 1)

            new_dept_panel += "{if $school_id == " + school_id_dict[school] + "}"
            new_dept_panel += '<div class="panel panel-info"><div class="panel-heading">'
            new_dept_panel += '<a href="{SITE_URL}?material/categorylist" class="glyphicon glyphicon-backward"></a>&nbsp;&nbsp;' + school;
            new_dept_panel += '</div><div class="panel-body"><table class="table" style="font-size:12px;">'

            for dept_index, (dept, major_list) in enumerate(dept_list):
                dept_id = '{0:0>3}'.format(dept_index + 1)

                if dept_index % DEPT_NUM_PER_LINE == 0:
                    new_dept_panel += "<tr>"

                dept_category = "D%s%s%s" % (region_id, school_id, dept_id)
                new_dept_panel += '<td width="' + str(99.0 / DEPT_NUM_PER_LINE) + '%"><a href="{SITE_URL}?material/category/' + dept_category +  '">' + dept + '</a></td>'

                if dept_index % DEPT_NUM_PER_LINE == DEPT_NUM_PER_LINE - 1:
                    new_dept_panel += "</tr>"

            if len(dept_list) % DEPT_NUM_PER_LINE != 0:
                new_dept_panel += ('<td width="' + str(99.0 / DEPT_NUM_PER_LINE) + '%"></td>') * (DEPT_NUM_PER_LINE - len(dept_list) % DEPT_NUM_PER_LINE)
                new_dept_panel += "</tr>"
            new_dept_panel = new_dept_panel + '</table></div></div>'

            new_dept_panel += "{/if}"

    print new_dept_panel

