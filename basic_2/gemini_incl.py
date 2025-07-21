import os
from dotenv import load_dotenv
from langchain_google_genai import ChatGoogleGenerativeAI

class AI:
    def __init__(self,key=None,mod = "gemini-2.5-flash",temp=0):
        if key is None:
            key = os.environ.get('GOOGLE_API_KEY')
        self.model = mod
        self.temp = temp
        self.llm = None
        if not(self.key_setup()):
            self.model = False
            self.temp = self.model
        else:
            self.llm = ChatGoogleGenerativeAI(model = self.model,temperature = self.temp)
    
    def key_setup(self):
        if "GOOGLE_API_KEY" not in os.environ:
            print("Error in environment key, using .env instead")
            load_dotenv("../.env")
            self.key = os.getenv("GOOGLE_API_KEY")
            print(self.key)
            if type(self.key) != type("str"):
                print("The value in .env file is incorrect, the program will stop")
                return False
        return True
        
    def invoke_abs(self, sources, type=0, edu=False):
        preamble = build_preamble()
        postamble = build_postamble()
        prompt = build_prompt_abs(edu)
        input_specs = build_input_specs()
        ex_input = build_ex_input()
        output_specs = build_output_specs(type, preamble, postamble)
        if type:
            ex_output = build_ex_output()
            full_prompt = f"{prompt}\n{input_specs}\n{ex_input}\n{output_specs}\n{ex_output}\nSources : {sources}"
        else:
            full_prompt = f"{prompt}\n{input_specs}\n{ex_input}\n{output_specs}\nSources : {sources}"
        
        print(f"full_prompt : {full_prompt}")
        return call_llm(self.llm, full_prompt)

    def build_quiz_prompt(self):
        return ("You are an expert academic engine specialized in quiz creation. Generate a multiple-choice quiz "
                "based on the following array of strings, in which each index is the text of a source, while using the same language as the sources. "
                "Ensure that any mathematical expressions or equations are formatted using LaTeX syntax.")

    def build_quiz_input_specs(self):
        return ("**Input:** You will be provided with an array of strings representing the text of all "
                "source documents. Each source text will be clearly delimited by `---SOURCE_START---` and `---SOURCE_END---` and have its index. "
                "Example: ---SOURCE_START---[Full text of Source 1]---SOURCE_END---")

    def build_quiz_param(self):
        return ("**Quiz Parameters:**"
                "1. **Number of questions:** Between 20 and 50 depending on the need."
                "2. **Propositions per Question:** Alternating between 2, 3 and 4 depending on the need of the question."
                "3. **Distractor Quality:** Ensure incorrect options (distractors) are plausible but clearly incorrect. "
                "They should ideally be derived from the source material (e.g., misinterpretations, details from other "
                "parts of the text) or common misconceptions related to the topic, rather than obviously wrong or irrelevant."
                "4. **Question Types:** Vary the question types to test different aspects of understanding (e.g., factual recall, "
                "inferential reasoning, understanding of key concepts, identification of main ideas, vocabulary). "
                "5. **Tone:** Maintain a clear, concise, and objective tone."
                "6. **Maths or special characters:** When the question requires a mathematical expression or a special character (like $\\omega$, $\\emptyset$, $\\sqrt{2}$, etc.), use LaTeX."
                )

    def build_quiz_output_format(self):
        return ("**Output Format:** The output must be format that can be saved directly with as much json "
                "files as there is sources like the following :")

    def build_quiz_ex_json_file(self):
        current_directory = os.path.dirname(os.path.abspath(__file__))
        with open(os.path.join(current_directory, "ex.json"), "r", encoding="utf-8") as f:
            return f.read()

    def invoke_quiz(self, sources, type=0):
        prompt = self.build_quiz_prompt()
        input_specs = self.build_quiz_input_specs()
        quiz_param = self.build_quiz_param()
        output_format = self.build_quiz_output_format()
        ex_json_file = self.build_quiz_ex_json_file()
        if type == 1:
            add = ("Create one quiz for each source file given. Separate the quiz for each source by a ---QUIZ_START--- and ---QUIZ_END---"
                   "with the format like : ---QUIZ_START--- [quiz of Source 1 in json] ---QUIZ_END--- ---QUIZ_START--- [quiz of Source 2 in json] ---QUIZ_END---")
            full_prompt = f"{prompt}\n{add}\n{input_specs}\n{quiz_param}\n{output_format}\n{ex_json_file}\nSources : {sources}"
        else:
            full_prompt = f"{prompt}\n{input_specs}\n{quiz_param}\n{output_format}\n{ex_json_file}\nSources : {sources}"
        print(f"full_prompt : {full_prompt}")
        return call_llm(self.llm, full_prompt)

def build_preamble():
        return r"""
    \documentclass[12pt]{article}
    \usepackage[margin=0.5in]{geometry}
    \usepackage{amsmath, amssymb}
    \usepackage{lmodern}
    \usepackage{fancyhdr}
    \pagestyle{fancy}
    \fancyhf{}
    \rfoot{\thepage}
    \renewcommand{\headrulewidth}{0pt}
    \usepackage{setspace}
    \onehalfspacing
    \usepackage{microtype}
    \usepackage[colorlinks=true, linkcolor=blue, urlcolor=blue, citecolor=blue]{hyperref}
    """

def build_postamble():
        return r"""
    \end{document}
    """

def build_prompt_abs(edu):
    if edu:
        return ("You are an expert teaching engine specialized in creating educational content. Your task is to create "
                "a comprehensive and coherent abstract that synthesizes the key information, main arguments, findings, "
                "and conclusions presented in the provided text.")
    else:
        return ("You are an expert academic summarizer and synthesis engine. Your task is to create a comprehensive "
                "and coherent abstract that synthesizes the key information, main arguments, findings, and conclusions presented "
                "in the provided text.")

def build_input_specs():
    return ("**Input:** You will be provided with an array of strings representing the text of all "
            "source documents. Each source text will be clearly delimited by `---SOURCE_START---` and `---SOURCE_END---` and will have its index.")

def build_ex_input():
    return "**Example of Input Format in each index:** ---SOURCE_START---[Full text of Source 1]---SOURCE_END---"

def build_output_specs(type, preamble, postamble):
    common_requirements = (
        "**Output Requirements:**\n"
        "1. **Content:**\n"
        "   - Identify the overarching theme, research question, or central topic addressed.\n"
        "   - Synthesize the primary arguments, methodologies (if relevant), and key findings or insights.\n"
        "   - Highlight any significant agreements, disagreements, or different perspectives that emerge from the texts.\n"
        "   - Conclude with the overall implications, a summary of the collective insights, or the main takeaway.\n"
        "   - Include formulas and important mathematical relations or terms of equations if considered important.\n"
        "2. **Format & Style:**\n"
        "   - The abstract should be organized in coherent paragraphs, with seamless transitions.\n"
        "   - Use appropriate LaTeX commands to structure the document with titles and sub-titles.\n"
        "   - Aim for a length of approximately one A4 page recto-verso (adjust this range based on the expected complexity and number of your sources).\n"
        "   - Maintain an objective, academic, and formal tone.\n"
        "   - Avoid direct quotes unless absolutely essential for conveying a specific concept, and if so, keep them very brief.\n"
        "   - Do not include citations or references within the abstract itself.\n"
        "   - Use the same language as the input source (e.g., source in French, write the summary in French).\n"
        f"3. **Result Format:** The result is to be written in LaTeX using utf-8 encoding including: {preamble} and {postamble}.\n"
    )

    if type:
        return (common_requirements +
                "   - Each source must have its abstract which must be encapsulated by: ---ABSTRACT START--- `abstract` ---ABSTRACT END--- allowing for easier post-treatment.\n"
                "   - Do not introduce the sources individually (e.g., 'Source 1 states...', 'According to the second text...') as it is repetitive, considering you analyze and summarize each source independently.\n")
    else:
        return (common_requirements +
                "   - Consider all sources as one single batch of information and do not present each source separately.\n"
                "   - The result must be encapsulated by: ---ABSTRACT START--- `abstract` ---ABSTRACT END--- allowing for easier post-treatment.\n")

def build_ex_output():
    current_directory = os.path.dirname(os.path.abspath(__file__))
    with open(os.path.join(current_directory, "latex_ex.tex"), "r", encoding="utf-8") as f:
        return f.read()


def call_llm(llm, prompt):
    return llm.invoke(prompt).content

        

def translate(choice):
    if choice == "mtpl" or choice == "educational": # Multiple abstract and educational
        return True
    else:# One document for all and professional
        return False
