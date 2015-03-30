
# -*- coding:utf-8 -*-

import urllib
import urllib2
import sys
try:
    from bs4 import BeautifulSoup
except:
    from BeautifulSoup import BeautifulSoup

url_search = 'http://wiki.963110.com.cn/index.php?search-default'
url_news = 'http://www.963110.com.cn/wcm/index.php?m=content&c=index&a=lists&catid=7'


def query_crop(name=""):
    """
        查询农作物信息
    """
    try:
        if name == "":
            return
        data = {"searchtext": "", "full": "1"}
        data["searchtext"] = name
        payload = urllib.urlencode(data)
        # print "payload: " + payload
        req = urllib2.urlopen(url_search, payload)
        line = req.read()
        # dump2file(line, "query.log")
        return extract_article(line)
    except BaseException, e:
        print e
    finally:
        pass


def query_crop_with_payload(name="", payload=""):
    """
        查询农作物信息
    """
    try:
        if name == "":
            return
        print "payload: " + payload
        req = urllib2.urlopen(url_search, payload)
        line = req.read()
        # dump2file(line, "query.log")
        article = extract_article(line)
        # print article
    except BaseException, e:
        print e
    finally:
        pass


def extract_article(page):
    if page is None:
        return None

    soup = BeautifulSoup(page)
    # print(soup.prettify().encode('utf-8'))
    wordcut = soup.find("div", {"class": "l w-710 o-v"}).find(
        "div", {"class": "content_1 wordcut"})
    article = wordcut.getText()
    return unicode(article).encode("utf-8")
    # print unicode(article)


def dump2file(data, filename, mode="w"):
    if data is not None and filename is not None:
        with open(filename, mode) as fp:
            fp.write(data)


# print(sys.argv[1].encode("utf-8"))
# print(unicode(sys.argv[1]).encode("utf-8"))
# print(sys.argv[1])
# print("你好")
print query_crop(sys.argv[1])
