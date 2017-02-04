import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;

import java.io.*;
import java.text.ParseException;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Set;

public class edgeGenerator {
    public static void main(String[] args) throws ParseException, IOException {


        BufferedReader br = new BufferedReader(new FileReader("map.csv"));
        String line = null;
        HashMap<String, String> fileUrlMap = new HashMap<String, String>();
        HashMap<String, String> urlFileMap = new HashMap<String, String>();
        while ((line = br.readLine()) != null) {
            String str[] = line.split(",");
            fileUrlMap.put(str[0], str[1]);
            urlFileMap.put(str[1], str[0]);
        }

        File dir = new File("/home/lastfighter/Dropbox/USC/CSCI572/Assignment 3/data/");
        Set<String> edges = new HashSet<String>();
        for (File file : dir.listFiles()) {
            Document doc = Jsoup.parse(file, "UTF-8", fileUrlMap.get(file.getName()));
            Elements links = doc.select("a[href]");
            Elements pngs = doc.select("[src]");

            for (Element link : links) {
                String url = link.attr("href").trim();
                if (urlFileMap.containsKey(url)) {
                    edges.add("/home/lastfighter/Dropbox/USC/CSCI572/Assignment3/data/"+file.getName() + " " + "/home/lastfighter/Dropbox/USC/CSCI572/Assignment3/data/"+urlFileMap.get(url));
                }
            }
        }

        File edgeList = new File("edgeList.txt");
        PrintWriter edgeWriter = new PrintWriter(edgeList);
        for (String s : edges) {
            edgeWriter.println(s);
        }
        edgeWriter.flush();
        edgeWriter.close();

    }
}


