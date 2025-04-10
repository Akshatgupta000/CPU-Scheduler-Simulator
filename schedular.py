import matplotlib.pyplot as plt
import numpy as np
import ipywidgets as widgets
from IPython.display import display, clear_output
import random

# Scheduler state
process_queue = []
completed_processes = []
current_time = 0
pid_counter = 1

# Widgets
burst_input = widgets.IntText(value=random.randint(1, 10), description="Burst Time:")
arrival_input = widgets.IntText(value=0, description="Arrival Time:")
mode_dropdown = widgets.Dropdown(
    options=["FCFS", "SJF", "RR"],
    value="FCFS",
    description="Mode:"
)
quantum_input = widgets.IntText(value=2, description="Quantum (RR):", visible=False)

# Process format: [pid, arrival, burst, remaining, start, end]
def add_process(_=None):
    global pid_counter
    burst = burst_input.value
    arrival = arrival_input.value
    proc = [pid_counter, arrival, burst, burst, None, None]
    process_queue.append(proc)
    pid_counter += 1
    update_display(f"Added P{proc[0]} (Arrival: {arrival}, Burst: {burst})")

def run_scheduler(_=None):
    global current_time, completed_processes, process_queue
    queue = sorted(process_queue, key=lambda p: p[1])  # by arrival time
    mode = mode_dropdown.value
    q = quantum_input.value

    if mode == "SJF":
        queue = sorted(queue, key=lambda p: p[2])
    elif mode == "RR":
        rr_queue = queue.copy()
        while rr_queue:
            proc = rr_queue.pop(0)
            if proc[4] is None:
                proc[4] = max(current_time, proc[1])
            exec_time = min(q, proc[3])
            current_time = max(current_time, proc[1]) + exec_time
            proc[3] -= exec_time
            if proc[3] == 0:
                proc[5] = current_time
                completed_processes.append(proc)
            else:
                rr_queue.append(proc)
        process_queue = []
        update_display("Executed Round Robin")
        return

    # FCFS or SJF
    for proc in queue:
        if proc[4] is None:
            proc[4] = max(current_time, proc[1])
        current_time = max(current_time, proc[1]) + proc[3]
        proc[3] = 0
        proc[5] = current_time
        completed_processes.append(proc)
    process_queue = []
    update_display(f"Executed {mode}")

def reset(_=None):
    global process_queue, completed_processes, current_time, pid_counter
    process_queue = []
    completed_processes = []
    current_time = 0
    pid_counter = 1
    update_display("Scheduler Reset")

def update_display(message=""):
    clear_output(wait=True)
    plot_schedule()
    if message:
        print(message)
    display(control_panel)

def plot_schedule():
    fig, ax = plt.subplots(figsize=(10, 2))
    for proc in completed_processes:
        pid, arrival, burst, _, start, end = proc
        ax.barh(0, end - start, left=start, color=np.random.rand(3,), edgecolor='black')
        ax.text(start + (end - start)/2, 0, f"P{pid}", ha='center', va='center', color='white')

    ax.set_xlim(0, max(current_time, 10))
    ax.set_ylim(-0.5, 0.5)
    ax.set_yticks([])
    ax.set_xlabel("Time")
    ax.set_title(f"{mode_dropdown.value} Scheduling - Gantt Chart")
    plt.show()

    print(f"Current Time: {current_time}")
    if process_queue:
        print("Pending Processes:")
        for p in process_queue:
            print(f"  P{p[0]} (Arrival: {p[1]}, Burst: {p[2]})")
    else:
        print("No pending processes.")

def update_quantum_visibility(change):
    quantum_input.layout.display = 'block' if change['new'] == "RR" else 'none'

# Attach the observer
mode_dropdown.observe(update_quantum_visibility, names='value')

# Initial quantum visibility setup
quantum_input.layout.display = 'none'

# Buttons
add_button = widgets.Button(description="Add Process", button_style='success')
run_button = widgets.Button(description="Run Scheduler", button_style='primary')
reset_button = widgets.Button(description="Reset", button_style='danger')

add_button.on_click(add_process)
run_button.on_click(run_scheduler)
reset_button.on_click(reset)

# Control panel
control_panel = widgets.VBox([
    widgets.HBox([burst_input, arrival_input, add_button]),
    widgets.HBox([mode_dropdown, quantum_input]),
    widgets.HBox([run_button, reset_button])
])

update_display()