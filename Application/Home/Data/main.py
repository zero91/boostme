#coding: utf8
import sys
import os

from argparse import ArgumentParser

SCHOOL_NUM_PER_LINE = 5
DEPT_NUM_PER_LINE = 3
MAJOR_NUM_PER_LINE = 4

def load_data(input_path):
    '''读取原始数据

       为了使得学校的ID编号一致，需要保持文件中的学校、专业等得出现顺序
    '''
    data_list = list()
    for line in open(input_path).xreadlines():
        line = line.strip()
        if line == "":
            continue

        cells = line.split('\t')
        if len(cells) != 4:
            continue

        region = cells[0].decode('utf-8')
        school = cells[1].decode('utf-8')
        dept = cells[2].decode('utf-8')

        if len(data_list) == 0 or data_list[-1][0] != region:
            data_list.append([region, list()])

        school_list = data_list[-1][1]
        if len(school_list) == 0 or school_list[-1][0] != school:
            school_list.append([school, list()])

        dept_list = school_list[-1][1]
        if len(dept_list) == 0 or dept_list[-1][0] != dept:
            dept_list.append([dept, map(lambda major: major.decode("utf-8"), cells[3].split(','))])
    return data_list

def process_js(data_list, output):
    ''' 根据文件中读取的学校、专业列表，生成js代码，供前端使用
    '''
    m_region_dict = dict()
    m_school_dict = dict()
    m_dept_dict = dict()
    m_major_dict = dict()

    region_id = school_id = dept_id = major_id = 0
    for region, school_list in data_list:
        region_id += 1
        m_region_dict[region_id] = {'name' : region, 'school_list' : list()}

        for school, dept_list in school_list:
            school_id += 1
            m_school_dict[school_id] = {'name' : school, 'dept_list' : list()}
            m_region_dict[region_id]['school_list'].append(school_id)

            for dept, major_list in dept_list:
                dept_id += 1
                m_dept_dict[dept_id] = {'name' : dept, 'major_list' : list()}
                m_school_dict[school_id]['dept_list'].append(dept_id)

                for major in major_list:
                    major_id += 1
                    m_major_dict[major_id] = {'name' : major}
                    m_dept_dict[dept_id]['major_list'].append(major_id)

    fout = open(output, 'w')
    #content = str(data_list).replace("u'", "'").decode('unicode-escape').encode("utf8")
    fout.write("var m_region_dict = %s;" % str(m_region_dict).replace("u'", "'").encode("utf8"))
    fout.write("var m_school_dict = %s;" % str(m_school_dict).replace("u'", "'").encode("utf8"))
    fout.write("var m_dept_dict = %s;" % str(m_dept_dict).replace("u'", "'").encode("utf8"))
    fout.write("var m_major_dict = %s;" % str(m_major_dict).replace("u'", "'").encode("utf8"))
    return True

def parse_args():
    arg_parser = ArgumentParser()
    arg_parser.add_argument("-i", "--input", default="../data_in/school_department_major.txt",
                                            help="Source data to be dealt with")

    arg_parser.add_argument("-o", "--output", default="../data_out/category.data.js",
                                            help="Output path")
    return arg_parser.parse_args()

if __name__ == "__main__":
    args = parse_args()

    data_list = load_data(args.input)

    process_js(data_list, args.output)

