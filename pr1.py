import psutil
import psycopg2
import datetime
import time
import schedule

def connect_to_db(db_name, user_name, passw, host_name):
    return psycopg2.connect(dbname=db_name, user=user_name, password=passw, host=host_name)

def insert_shot(conn, result, num_of_procs):
    sql = 'INSERT INTO shots(shot_time, memory_free, memory_used, num_of_procs) \
    VALUES (\'' + str(result[0]) + '\', ' + str(result[1]) + ', ' + str(result[2]) + ', \'' + str(num_of_procs) +'\')'
    conn.cursor().execute(sql)
    conn.commit()

def insert_procs(conn, result, timestamp):
    for res in result:
        sql = 'INSERT INTO procs(shot_time, proc_exe, memory_usage, create_time, threads, proc_id, proc_args) \
        VALUES (\'' + str(timestamp) + '\', \'' + str(res[0]) + '\', \'' + str(res[1]) + '\', \'' + str(res[2]) + '\', \
            ' + str(res[3]) + ', ' + str(res[4]) + ', \'' + str(res[5]) + '\')'
        print(sql)
        conn.cursor().execute(sql)
        conn.commit()

def get_procs():
    procs = []
    for process in psutil.process_iter():
        with process.oneshot():
            pid = process.pid
            if pid != 0:
                try:
                    cmd = process.cmdline()
                except psutil.AccessDenied:
                    continue
                try:
                    perc = process.num_threads()
                except psutil.AccessDenied:
                    continue
                try:
                    mem = process.memory_full_info().uss
                except psutil.AccessDenied:
                    continue
                try:
                    create_time = datetime.datetime. \
                    fromtimestamp(process.create_time())
                except OSError:
                    create_time = datetime.datetime. \
                    fromtimestamp(psutil.boot_time())
                cr_time = create_time.replace(microsecond=0)
                proc = (cmd.pop(0), mem, str(cr_time), \
                        perc, pid, str(cmd).replace('\'', ''))
                procs.append(proc)
    date_string = time.time()
    timestamp = datetime.datetime. \
    fromtimestamp(date_string).replace(microsecond=0)
    return (procs, timestamp)

def get_stats(timestamp):
    stats = (timestamp, psutil.virtual_memory().free, psutil.virtual_memory().percent)
    return stats

def task():
    conn = connect_to_db('practice', 'postgres', '8734', 'localhost')

    res = get_procs()
    insert_shot(conn, get_stats(res[1]), len(res[0]))
    insert_procs(conn, res[0], res[1])

def test_task():
    print('1')

schedule.every(3).seconds.do(task)

while True:
    schedule.run_pending()
    time.sleep(1)

# для паузы: ctrl+c в консоли / shift+f5 в дебаге