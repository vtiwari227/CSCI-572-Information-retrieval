/**
 * Created by lastfighter on 11/17/16.
 */

   import java.io.File;
   import java.io.FileInputStream;
   import java.io.IOException;
   import java.io.PrintWriter;

   import org.apache.tika.exception.TikaException;
   import org.apache.tika.metadata.Metadata;
   import org.apache.tika.parser.ParseContext;
   import org.apache.tika.parser.html.HtmlParser;
   import org.apache.tika.sax.BodyContentHandler;

           import org.xml.sax.SAXException;

public class contentGenerator {

    public static void main(final String[] args) throws IOException,SAXException, TikaException {
        PrintWriter out = new PrintWriter("big.txt");
        File dir = new File("/home/lastfighter/Documents/USC/CSCI572/data/");
        for (File file : dir.listFiles()) {

            //detecting the file type
            BodyContentHandler handler = new BodyContentHandler(-1);
            Metadata metadata = new Metadata();
            FileInputStream inputstream = new FileInputStream(file);
            ParseContext pcontext = new ParseContext();

            //Html parser
            HtmlParser htmlparser = new HtmlParser();
            htmlparser.parse(inputstream, handler, metadata, pcontext);


            out.println(handler.toString());
            String[] metadataNames = metadata.names();
            out.println(metadataNames);

            }
        out.close();
        out.flush();
        }
    }

