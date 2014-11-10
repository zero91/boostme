#coding: utf-8

import sys
import re
import urllib2
from optparse import OptionParser
from bs4 import BeautifulSoup

def get_school_major_list(school_major_html_doc):
    soup = BeautifulSoup(school_major_html_doc)

    tc2_list = soup.find_all('div', class_='tc2')
    tc39_list = soup.find_all('div', class_='tc39')

    department = soup.find('div', class_='tc2')
    major = soup.find('div', class_='tc39')

    result = []
    while True:
        department_name = department.text.strip()
        department = department.find_next_sibling('div', class_='tc2')

        major_list = []
        while True:
            for li in major.find_all('li'):
                major_list.append(li.a.text.strip())
            
            major = major.find_next_sibling('div', class_='tc39')

            if major == None or (department != None and major.previous_element.strip() == department.text.strip()):
                break

        result.append([department_name, ",".join(major_list)])
        if department == None or major == None:
            break

    return result

def get_school_link_list(school_fname):
    html_doc = "\n".join(open(school_fname, 'r').readlines())
    soup = BeautifulSoup(html_doc)

    tc2_list = soup.find_all('div', class_='tc2')
    tc3_list = soup.find_all('div', class_='tc3')
    #soup.a.encode(formatter=None)

    if len(tc2_list) != len(tc3_list):
        sys.stderr.write("Province and school doesn't match, please check the html file")
        return

    region = soup.find('div', class_='tc2')
    school = soup.find('div', class_='tc3')

    result = []
    while True:
        region_name = region.a.text
        school_list = [li.a.text for li in school.ul.find_all('li')]

        sys.stderr.write("%s\n" % region_name)

        for li in school.ul.find_all('li'):
            if not re.match("http://tcool.chinakaoyan.com/*", li.a['href']):
                continue

            school_html = urllib2.urlopen(li.a['href'], timeout=120).read()
            school_name = li.a.text
            sys.stderr.write("\t%s\n" % school_name)

            #school_major_list = get_school_major_list(unicode(school_html, "gbk").encode("utf8"))
            school_major_list = get_school_major_list(school_html)

            for department,major in school_major_list:
                result.append('%s\t%s\t%s\t%s' % (region_name, school_name, department, major))

        region = region.find_next_sibling('div', class_='tc2')
        school = school.find_next_sibling('div', class_='tc3')
        if region == None or school == None:
            break

    return result

if __name__ == '__main__':
    usage = "Usage: python %prog [options]\nVersion 1.0 by zhangjian\n\ne.g.:\t(1) output school major category list:\tpython %prog -f school_index.html"
    parser = OptionParser(usage=usage)

    parser.add_option("-f", "--fname", action="store", default="school_index.html", dest="school_fname",
            help="The HTML file of school list", type="string")

    (options, args) = parser.parse_args()

    school_deparment_major_list = get_school_link_list(options.school_fname)

    sys.stdout.write("%s\n" % "\n".join(school_deparment_major_list).encode('utf-8'))

