import networkx as nx
import csv
with open('edgeList.csv', 'rb') as csvfile:
     spamreader = csv.reader(csvfile, delimiter=' ', quotechar='|')
     G = nx.DiGraph()
     for row in spamreader:
         list = ','.join(row).strip().split(",")
         source = list[0]
         for item in list[1:]:   # Python indexes start at zero
            G.add_edge(source, item)

dict = nx.pagerank(G, alpha=0.85, personalization=None, max_iter=30, tol=1e-06, nstart=None, weight='weight',dangling=None)
dictlist = {}
for key, value in dict.iteritems():
    dictlist[key] = value
   

fp = open('external_PageRankFile.txt', 'w+') 
for k,v in dictlist.iteritems():
	fp.write('{} = {}'.format(k,v))
	fp.write("\n")
fp.close();
