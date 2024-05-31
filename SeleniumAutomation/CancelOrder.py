from selenium import webdriver
from selenium.webdriver.common.by import By
import time

browser = webdriver.Chrome()

browser.get("https://www.muchmade.id.vn/wp-admin/edit.php?post_type=shop_order")

userName = browser.find_element(By.CSS_SELECTOR, "form #user_login")
userName.send_keys("muchmade")

userPass = browser.find_element(By.CSS_SELECTOR, "form #user_pass")
userPass.send_keys("admin123")

loginBtn = browser.find_element(By.CSS_SELECTOR, "#wp-submit")
loginBtn.click()

time.sleep(5)

dropDown = browser.find_element(By.CSS_SELECTOR, "#bulk-action-selector-top")
dropDown.click()

cancelBtn = browser.find_element(By.CSS_SELECTOR, "option[value='mark_cancelled']")
cancelBtn.click()

checkBox = browser.find_element(By.CSS_SELECTOR, "#the-list tr:nth-child(1) th:nth-child(1) input[type='checkbox']")
checkBox.click()

submitBtn = browser.find_element(By.CSS_SELECTOR, ".top #doaction")
submitBtn.click()
