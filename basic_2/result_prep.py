import subprocess
import os
import re
import shutil
import json
from fpdf import FPDF

DOWNLOAD_DIR = "download"

def to_json(input_str, filename):
    '''Function to transform and save a string into a json file'''
    os.makedirs(DOWNLOAD_DIR, exist_ok=True)
    data = json.loads(input_str)
    file_path = os.path.join(DOWNLOAD_DIR,filename)
    with open(file_path, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=4)
    return file_path

def clean_split_str(input : str):
    res_tab = input.split("---ABSTRACT END---")
    res_tab.pop()
    for i in range (0,len(res_tab)):
        res_tab[i] = res_tab[i].replace("---ABSTRACT START---",'')
        res_tab[i] = res_tab[i].replace("---ABSTRACT END---",'')
        res_tab[i] = res_tab[i].replace("```latex",'')
        res_tab[i] = res_tab[i].replace("```",'')
    return res_tab

def clean_json(input : str):
    tab = input.split("---QUIZ_END---")
    tab.pop()
    for i in range(0,len(tab)):
        tab[i] = tab[i].replace("---QUIZ_START---",'')
        tab[i] = tab[i].replace("```json",'')
        tab[i] = tab[i].replace("```",'')
    return tab


def to_pdf(input: str, filename: str):
    '''
    This function takes a LaTeX string and converts it to a PDF file.
    The PDF and .tex are saved in the 'download' directory with the given filename.
    All temporary files are created in the same directory as this code.
    
    Returns:
        str: The full path to the generated PDF file
    '''
    #input = process_latex(input, filename)
    script_dir = os.path.dirname(os.path.abspath(__file__))
    os.makedirs(DOWNLOAD_DIR, exist_ok=True)
    # Use a temp directory inside the script directory
    temp_dir = os.path.join(script_dir, "_temp_pdflatex")
    os.makedirs(temp_dir, exist_ok=True)
    try:
        tex_path = os.path.join(temp_dir, "temp.tex")
            
        with open(tex_path, "w", encoding="utf-8") as file:
            file.write(input)
        
        print(f"LaTeX file written to: {tex_path}")
        print(f"LaTeX content length: {len(input)} characters")
        
        try:
            print("Starting pdflatex compilation...")
            result = subprocess.run(
                ["pdflatex", "-interaction=nonstopmode", "temp.tex"],
                cwd=temp_dir,
                check=True,
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                text=True,  # This ensures text output instead of bytes
            )
            print("pdflatex compilation completed successfully")
            print(f"STDOUT: {result.stdout}")
            
        except subprocess.CalledProcessError as e:
            print("=" * 60)
            print("COMPILATION FAILED - DETAILED DEBUG INFO:")
            print("=" * 60)
            
            # Print return code
            print(f"Return code: {e.returncode}")
            
            # Print stdout and stderr
            print(f"STDOUT:\n{e.stdout}")
            print(f"STDERR:\n{e.stderr}")
            
            # Check and print log file
            log_path = os.path.join(temp_dir, "temp.log")
            if os.path.exists(log_path):
                print(f"LaTeX log file exists at: {log_path}")
                try:
                    with open(log_path, "r", encoding="utf-8") as log_file:
                        log_content = log_file.read()
                        print(f"LOG FILE CONTENT:\n{log_content}")
                except UnicodeDecodeError:
                    # Try with different encoding
                    with open(log_path, "r", encoding="latin-1") as log_file:
                        log_content = log_file.read()
                        print(f"LOG FILE CONTENT (latin-1):\n{log_content}")
            else:
                print("No log file found!")
            
            # Check what files were created
            print(f"Files in temp directory {temp_dir}:")
            for file in os.listdir(temp_dir):
                file_path = os.path.join(temp_dir, file)
                if os.path.isfile(file_path):
                    size = os.path.getsize(file_path)
                    print(f"  {file} ({size} bytes)")
            
            # Check if PDF was created
            temp_pdf_path = os.path.join(temp_dir, "temp.pdf")
            if os.path.exists(temp_pdf_path):
                print(f"PDF file exists: {temp_pdf_path}")
                print(f"PDF file size: {os.path.getsize(temp_pdf_path)} bytes")
            else:
                print("PDF file was NOT created!")
            
            # Show the first few lines of the LaTeX content for debugging
            print(f"First 500 characters of LaTeX content:")
            print(input[:500])
            print("=" * 60)
            
            # Create a more informative error message
            error_msg = f"LaTeX compilation failed with return code {e.returncode}"
            if e.stderr:
                error_msg += f"\nSTDERR: {e.stderr}"
            if e.stdout:
                error_msg += f"\nSTDOUT: {e.stdout}"
            
            raise RuntimeError(error_msg)
        
        temp_pdf_path = os.path.join(temp_dir, "temp.pdf")
        if os.path.exists(temp_pdf_path):
            print(f"PDF generated successfully: {temp_pdf_path}")
            shutil.copy(temp_pdf_path, os.path.join(script_dir, DOWNLOAD_DIR, filename))
            print(f"PDF copied to: {os.path.join(script_dir, DOWNLOAD_DIR, filename)}")
        else:
            print("PDF file was not generated despite successful compilation!")
            raise FileNotFoundError("PDF file was not generated.")
        
        # Save the .tex file as well
        tex_output_path = os.path.join(script_dir, DOWNLOAD_DIR, filename.replace("pdf", "tex"))
        shutil.copy(tex_path, tex_output_path)
        print(f"LaTeX file copied to: {tex_output_path}")
        
        # Return the path to the generated PDF
        #pdf_output_path = os.path.join(script_dir, DOWNLOAD_DIR, filename)
        #return pdf_output_path
        
    finally:
        # Clean up temp files
        print(f"Cleaning up temp directory: {temp_dir}")
        shutil.rmtree(temp_dir, ignore_errors=True)


def process_latex(latex_str: str, filename: str) -> str:
    """
    Replace LaTeX preamble and postamble, insert title as 'abstract_<filename>'.
    """

    new_preamble = f"""\\documentclass[a4paper,12pt]{{article}}
\\usepackage[utf8]{{inputenc}}
\\usepackage[T1]{{fontenc}}
\\usepackage{{amsmath,amsfonts,amssymb}}
\\usepackage{{graphicx}}
\\usepackage{{geometry}}
\\geometry{{a4paper, margin=1in}}
\\usepackage{{hyperref}}
\\hypersetup{{
    colorlinks=true,
    linkcolor=blue,
    filecolor=magenta,
    urlcolor=cyan,
}}
\\usepackage{{fancyhdr}}
\\pagestyle{{fancy}}
\\fancyhf{{}}
\\rhead{{\\thepage}}
\\renewcommand{{\\headrulewidth}}{{0.4pt}}
\\renewcommand{{\\footrulewidth}}{{0pt}}
\\setlength{{\\headheight}}{{14.5pt}}  % Fix fancyhdr warning
\\title{{abstract_{filename}}}
\\author{{}}  % Avoid "No \\author given" warning
\\date{{}}
\\begin{{document}}
\\maketitle
"""

    new_postamble = "\\end{{document}}"

    # Extract content between \begin{document} and \end{document}
    print("WE ARE HERE !!!! ")
    match = re.search(r"\\begin\{document\}(.*?)\\end\{document\}", latex_str, re.DOTALL)
    content = match.group(1).strip() if match else latex_str.strip()

    return f"{new_preamble}\n{content}\n{new_postamble}"
