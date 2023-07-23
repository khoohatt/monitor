<?php
    class DB { 
        public function dbConnect() {
            $host='localhost';
            $db = 'practice';
            $port = '5432';
            $username = 'postgres';
            $password = '8734';

            $dsn = "pgsql:host=$host;port=$port;dbname=$db;user=$username;password=$password";

            try {
                $conn = new PDO($dsn);
                if($conn) {
                    //echo("<script>console.log('connected');</script>");
                    return $conn;
                }
            } catch (PDOException $e) {
                echo $e -> getMessage();
            }
        }
    
        public function dbSelectStats() {
            $sql = 'select * from public.shots order by shot_time desc limit 1';
            $conn = DB::dbConnect();
            $res = $conn -> query($sql);
            $conn = null;
            return $res;
        }

        public function dbSelect() {
            $sql = 'select * from public.shots
                join public.procs ON procs.shot_time = shots.shot_time
                group by procs.shot_time, shots.shot_time, procs.proc_exe, procs.memory_usage, procs.create_time, procs.threads, procs.proc_id, procs.proc_args
                having shots.shot_time = (select shot_time from public.shots order by shot_time desc limit 1)
                order by procs.memory_usage desc';

            $conn = DB::dbConnect();
            $res = $conn -> query($sql);
            $conn = null;
            return $res;
        }

        public function dbSelectHistory($shot_time) {
            $sql = 'select * from shots 
                join public.procs ON procs.shot_time = shots.shot_time
                group by procs.shot_time, shots.shot_time, procs.proc_exe, procs.memory_usage, procs.create_time, procs.threads, procs.proc_id, procs.proc_args
                having procs.shot_time = (SELECT shot_time FROM shots 
                order by abs(DATE_PART(\'day\', shot_time::timestamp - \'' . $shot_time . '\'::timestamp) * 1440 
                + DATE_PART(\'hour\', shot_time::timestamp - \'' . $shot_time . '\'::timestamp) * 60 
                + DATE_PART(\'minute\', shot_time::timestamp - \'' . $shot_time . '\'::timestamp) 
                + DATE_PART(\'second\', shot_time::timestamp - \'' . $shot_time . '\'::timestamp) / 60) 
                asc limit 1)';

            $conn = DB::dbConnect();
            $res = $conn -> query($sql);
            $conn = null;
            return $res;
        }
    }
?>